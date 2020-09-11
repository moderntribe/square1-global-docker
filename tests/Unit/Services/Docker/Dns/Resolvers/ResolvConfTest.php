<?php

namespace Tests\Unit\Services\Docker\Dns\Resolvers;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Commands\LocalDocker\Start;
use Illuminate\Filesystem\Filesystem;
use App\Services\Docker\Dns\Resolvers\ResolvConf;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ResolvConfTest extends TestCase {

    private $runner;
    private $filesystem;
    private $file;
    private $command;

    protected function setUp(): void {
        parent::setUp();

        $this->runner     = $this->mock( CommandRunner::class );
        $this->filesystem = $this->mock( Filesystem::class );
        $this->file       = storage_path( 'tests/resolv.conf.head' );
        $this->command    = $this->mock( Start::class );
    }

    public function test_it_is_supported() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->with( '/etc/resolv.conf' )
                         ->once()
                         ->andReturnTrue();

        $dhcp = new ResolvConf( $this->runner, $this->filesystem, $this->file );

        $this->assertTrue( $dhcp->supported() );
    }

    public function test_it_is_not_supported() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->with( '/etc/resolv.conf' )
                         ->once()
                         ->andReturnFalse();

        $dhcp = new ResolvConf( $this->runner, $this->filesystem, $this->file );

        $this->assertFalse( $dhcp->supported() );
    }

    public function test_it_is_enabled() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( $this->file )
                         ->once()
                         ->andReturn( 'nameserver 127.0.0.1' );

        $dhcp = new ResolvConf( $this->runner, $this->filesystem, $this->file );

        $this->assertTrue( $dhcp->enabled() );
    }

    public function test_it_is_disabled_with_invalid_nameservers() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( $this->file )
                         ->once()
                         ->andReturn( 'nameserver 10.0.0.1' );

        $dhcp = new ResolvConf( $this->runner, $this->filesystem, $this->file );

        $this->assertFalse( $dhcp->enabled() );
    }

    public function test_it_is_disabled_with_missing_resolv_conf() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( $this->file )
                         ->once()
                         ->andThrow( FileNotFoundException::class );

        $dhcp = new ResolvConf( $this->runner, $this->filesystem, $this->file );

        $this->assertFalse( $dhcp->enabled() );
    }

    public function test_it_can_be_enabled() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'echo "nameserver 127.0.0.1" | sudo tee ' . $this->file )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();

        $this->command->shouldReceive( 'task' )
                      ->with( sprintf( '<comment>➜ Adding 127.0.0.1 nameservers to %s</comment>', $this->file ), null )
                      ->once()
                      ->andReturnTrue();

        $dhcp = new ResolvConf( $this->runner, $this->filesystem, $this->file );

        $dhcp->enable( $this->command );
    }

}
