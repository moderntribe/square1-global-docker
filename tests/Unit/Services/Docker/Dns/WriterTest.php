<?php

namespace Tests\Unit\Services\Docker\Dns;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Services\Docker\Dns\Writer;
use Symfony\Component\Filesystem\Filesystem;

class WriterTest extends TestCase {

    public function test_it_writes_resolver() {
        $tmpFile    = storage_path( 'tests/tmp/sq1_rand' );
        $configFile = storage_path( 'tests/head.conf' );
        $directory  = storage_path( 'tests/' );

        $runner = $this->mock( CommandRunner::class );
        $runner->shouldReceive( 'with' )->twice()->andReturn( $runner );
        $runner->shouldReceive( 'run' )->with( 'sudo mkdir -p {{ $directory }}' )->once()->andReturn( $runner );
        $runner->shouldReceive( 'run' )->with( 'sudo cp {{ $from }} {{ $to }}' )->once()->andReturn( $runner );
        $runner->shouldReceive( 'throw' )->twice()->andReturn( $runner );

        $filesystem = $this->mock( Filesystem::class );
        $filesystem->shouldReceive( 'tempnam' )->once()->with( '/tmp', 'sq1' )->andReturn( $tmpFile );
        $filesystem->shouldReceive( 'dumpFile' )->with( $tmpFile, 'nameserver 127.0.1.1' )->once();
        $filesystem->shouldReceive( 'chmod' )->once();
        // Fake the directory doesn't exist
        $filesystem->shouldReceive( 'exists' )->with( $directory )->once()->andReturn( false );

        $result = ( new Writer( $runner, $filesystem ) )->write( $configFile, $directory, '127.0.1.1' );

        $this->assertTrue( $result );
    }
}
