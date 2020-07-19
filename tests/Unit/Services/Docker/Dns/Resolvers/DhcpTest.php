<?php

namespace Tests\Unit\Services\Docker\Dns\Resolvers;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Commands\GlobalDocker\Start;
use Illuminate\Filesystem\Filesystem;
use App\Services\Docker\Dns\Resolvers\Dhcp;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class DhcpTest extends TestCase {

    private $runner;
    private $filesystem;
    private $command;

    public function setUp(): void {
        parent::setUp();

        $this->runner     = $this->mock( CommandRunner::class );
        $this->filesystem = $this->mock( Filesystem::class );
        $this->command    = $this->mock( Start::class );
    }

    public function test_it_is_supported_when_network_manager_is_active() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'systemctl status NetworkManager' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturnTrue();

        $this->runner->shouldReceive( '__toString' )
                     ->once()
                     ->andReturn( 'Sample NetworkManger Output... active (running)' );

        $resolver = new Dhcp( $this->runner, $this->filesystem );

        $this->assertTrue( $resolver->supported() );
    }

    public function test_it_is_not_supported_when_network_manger_is_disabled() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'systemctl status NetworkManager' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturn( true );

        $this->runner->shouldReceive( '__toString' )
                     ->once()
                     ->andReturn( 'Sample NetworkManger Output...' );

        $resolver = new Dhcp( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->supported() );
    }

    public function test_it_is_not_supported_when_network_manager_is_not_installed() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'systemctl status NetworkManager' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturnFalse();

        $resolver = new Dhcp( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->supported() );
    }

    public function test_it_enables_dhcp() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( '/etc/dhcp/dhclient.conf' )
                         ->once()
                         ->andReturn( 'prepend domain-name-servers 127.0.0.1;' );

        $resolver = new Dhcp( $this->runner, $this->filesystem );

        $this->assertTrue( $resolver->enabled() );
    }

    public function test_it_is_disabled_with_missing_content() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( '/etc/dhcp/dhclient.conf' )
                         ->once()
                         ->andReturn( '# some random stuff' );

        $resolver = new Dhcp( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->enabled() );
    }

    public function test_it_is_disabled_with_missing_dhcp_conf() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( '/etc/dhcp/dhclient.conf' )
                         ->once()
                         ->andThrow( FileNotFoundException::class );

        $resolver = new Dhcp( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->enabled() );
    }

    public function test_it_can_be_enabled() {
        $this->filesystem->shouldReceive( 'exists' )->with( '/etc/dhcp' )->once()->andReturnFalse();

        $this->runner->shouldReceive( 'run' )->with( 'sudo mkdir /etc/dhcp' )->once()->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->with( 'echo "prepend domain-name-servers 127.0.0.1;" | sudo tee -a /etc/dhcp/dhclient.conf' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->with( 'sudo systemctl restart NetworkManager' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->times( 3 )->andReturnSelf();

        $this->command->shouldReceive( 'task' )
                      ->with( '<comment>➜ Creating folder /etc/dhcp</comment>', null )
                      ->once()
                      ->andReturnTrue();

        $this->command->shouldReceive( 'task' )
                      ->with( '<comment>➜ Adding 127.0.0.1 nameservers to /etc/dhcp/dhclient.conf</comment>', null )
                      ->once()
                      ->andReturnTrue();

        $this->command->shouldReceive( 'task' )
                      ->with( '<comment>➜ Restarting NetworkManager</comment>', null )
                      ->once()
                      ->andReturnTrue();

        $resolver = new Dhcp( $this->runner, $this->filesystem );

        $resolver->enable( $this->command );
    }

}
