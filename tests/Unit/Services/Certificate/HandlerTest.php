<?php

namespace Tests\Unit\Services\Certificate;

use Tests\TestCase;
use InvalidArgumentException;
use App\Services\Certificate\Ca;
use App\Services\Certificate\Certificate;
use App\Services\Certificate\Handler;
use Illuminate\Filesystem\Filesystem;

class HandlerTest extends TestCase {

    protected $ca;
    protected $certificate;
    protected $filesystem;
    protected $certFolder;

    public function setUp(): void {
        parent::setUp();

        $this->ca          = $this->mock( Ca::class );
        $this->certificate = $this->mock( Certificate::class );
        $this->filesystem  = $this->mock( Filesystem::class );
        $this->certFolder  = storage_path( 'tests/certs' );
    }

    public function test_it_creates_ca_certificate() {
        $this->ca->shouldReceive( 'create' )
                 ->with( $this->certFolder )
                 ->once();

        $this->ca->shouldReceive( 'installed' )
                 ->once()
                 ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->certFolder . '/tribeCA.pem' )
                         ->andReturnTrue();

        $handler = new Handler( $this->ca, $this->certificate, $this->filesystem, $this->certFolder );

        $this->assertFalse( $handler->caExists() );

        $handler->createCa();
    }

    public function test_it_creates_certificate() {
        $domain = 'squareone.tribe';
        $file   = $this->certFolder . '/' . $domain . '.crt';

        $this->certificate->shouldReceive( 'exists' )
                          ->with( $file )
                          ->once()
                          ->andReturn( false );

        $this->certificate->shouldReceive( 'create' )
                          ->with( $domain, $this->certFolder )
                          ->once();

        $handler = new Handler( $this->ca, $this->certificate, $this->filesystem, $this->certFolder );

        $handler->createCertificate( $domain );
    }

    public function test_it_throws_execption_with_invalid_domain() {
        $this->expectException( InvalidArgumentException::class );

        $handler = new Handler( $this->ca, $this->certificate, $this->filesystem, $this->certFolder );

        $handler->createCertificate( '' );
    }

}
