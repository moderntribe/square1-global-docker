<?php

namespace Tests\Unit\Services\Certificate;

use App\Runners\CommandRunner;
use App\Services\Certificate\Ca;
use App\Services\Certificate\Trust\Strategies\Linux;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class CaTest extends TestCase {

    private $filesystem;
    private $runner;
    private $trust;

    public function setUp(): void {
        parent::setUp();

        $this->filesystem = $this->mock( Filesystem::class );
        $this->runner     = $this->mock( CommandRunner::class );
        $this->trust      = $this->mock( Linux::class );
    }

    public function test_it_creates_ca_certificate() {
        $savePath = storage_path( 'tests/ca' );

        $this->trust->shouldReceive( 'trustCa' )->with( $savePath . '/' . Ca::PEM_NAME )->once();

        $this->filesystem->shouldReceive( 'exists' )
                   ->with( $savePath . '/' . Ca::KEY_NAME )
                   ->once()
                   ->andReturn( false );

        $this->filesystem->shouldReceive( 'exists' )
                   ->with( $savePath . '/' . Ca::PEM_NAME )
                   ->once()
                   ->andReturn( false );

        $this->runner->shouldReceive( 'with' )->once()->andReturn( $this->runner );

        $this->runner->shouldReceive( 'run' )
               ->with( 'openssl req -x509 -new -nodes -sha256 -newkey rsa:4096 -days {{ $days }} -keyout {{ $keyout }} -out {{ $out }} -subj {{ $subj }}' )
               ->once()
               ->andReturn( $this->runner );

        $this->runner->shouldReceive( 'throw' )->once();


        $ca = new Ca( $this->trust, $this->filesystem, $this->runner );

        $ca->create( $savePath, 10, true );
    }

    public function test_it_detects_installed_ca() {
        $this->trust->shouldReceive( 'installed' )->once()->andReturnTrue();

        $ca = new Ca( $this->trust, $this->filesystem, $this->runner );

        $this->assertTrue( $ca->installed() );
    }

    public function test_it_throws_exception_when_ca_exists() {
        $this->expectException( \RuntimeException::class );

        $savePath = storage_path( 'tests/ca' );

        $this->filesystem->shouldReceive( 'exists' )
                   ->with( $savePath . '/' . Ca::KEY_NAME )
                   ->once()
                   ->andReturn( true );

        $ca = new Ca( $this->trust, $this->filesystem, $this->runner );

        $ca->create( $savePath, 10, true );


    }

}
