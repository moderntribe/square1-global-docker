<?php

namespace Tests\Unit\Services\Certificate\Trust;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Services\Certificate\Trust\Strategies\MacOs;


class MacOsTest extends TestCase {

    private $runner;
    private $macOsTrust;
    private $crt;

    public function setUp(): void {
        parent::setUp();

        $this->runner     = $this->mock( CommandRunner::class );
        $this->macOsTrust = $this->app->make( MacOs::class );
        $this->crt        = storage_path( 'tests/tribeCa.pem' );
    }

    public function test_it_detects_installed_ca_certificates() {
        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with('security find-certificate -c tri.be')
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $this->assertTrue( $this->macOsTrust->installed() );
    }

    public function test_it_trust_ca_certificate() {
        $this->runner->shouldReceive( 'with' )
                     ->with( [ 'crt'  => $this->crt ] )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->with( 'sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain {{ $crt }}' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $this->macOsTrust->trustCa( $this->crt );
    }

    public function test_it_trusts_certificate() {
        $this->runner->shouldReceive( 'with' )
                     ->with( [ 'crt'  => $this->crt ] )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->with( 'sudo security add-trusted-cert -d -r trustAsRoot -k /Library/Keychains/System.keychain {{ $crt }}' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $this->macOsTrust->trustCertificate( $this->crt );
    }

}
