<?php

namespace Tests\Unit\Services\Docker\Dns\Resolvers;

use Tests\TestCase;
use App\Runners\CommandRunner;
use Illuminate\Filesystem\Filesystem;
use App\Services\Docker\Dns\Resolvers\Scutil;

class ScutilTest extends TestCase {

    private $runner;
    private $filesystem;
    private $file;

    protected function setUp(): void {
        parent::setUp();

        $this->runner     = $this->mock( CommandRunner::class );
        $this->filesystem = $this->mock( Filesystem::class );
        $this->file       = storage_path( 'tests/resolv.conf.head' );
    }


    public function test_it_is_supported() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'scutil --dns' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturnTrue();

        $resolver = new Scutil( $this->runner, $this->filesystem, $this->file );

        $this->assertTrue( $resolver->supported() );
    }

    public function test_it_is_not_supported() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'scutil --dns' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturnFalse();

        $resolver = new Scutil( $this->runner, $this->filesystem, $this->file );

        $this->assertFalse( $resolver->supported() );
    }
}
