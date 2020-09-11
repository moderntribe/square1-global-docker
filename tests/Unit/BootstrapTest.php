<?php

namespace Tests\Unit;

use App\Bootstrap;
use Tests\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class BootstrapTest extends TestCase {

    public function test_it_copies_global_directory() {
        $configDir = storage_path( 'tests' );

        $filesystem = $this->mock( Filesystem::class );
        $filesystem->shouldReceive( 'exists' )
                   ->once()
                   ->with( $configDir . '/' . Bootstrap::GLOBAL_DIR )
                   ->andReturn( false );

        $filesystem->shouldReceive( 'mirror' )
                   ->once()
                   ->with( storage_path( 'global/' ), $configDir . '/' . Bootstrap::GLOBAL_DIR );

        $filesystem->shouldReceive( 'exists' )
                   ->once()
                   ->with( storage_path( 'tests/defaults' ) )
                   ->andReturnFalse();

        $filesystem->shouldReceive( 'exists' )
                   ->once()
                   ->with(  storage_path( 'tests/store' ) )
                   ->andReturnFalse();

        $filesystem->shouldReceive( 'mkdir' )
                   ->once()
                   ->with( storage_path( 'tests/defaults' ), 0755 );

        $filesystem->shouldReceive( 'mkdir' )
                   ->once()
                   ->with( storage_path( 'tests/store' ), 0755 );

        $bootstrap = new Bootstrap( $configDir, $filesystem );

        $bootstrap->boot();
    }
}
