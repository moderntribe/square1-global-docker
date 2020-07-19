<?php

namespace Tests\Unit\Services\Certificate\Trust;

use Tests\TestCase;
use App\Runners\CommandRunner;
use Illuminate\Filesystem\Filesystem;
use App\Services\Certificate\Trust\LinuxTrustStore;
use App\Services\Certificate\Trust\Strategies\Linux;


class LinuxTest extends TestCase {

    private $trustStores;
    private $filesystem;
    private $runner;
    private $pem;

    public function setUp(): void {
        parent::setUp();

        $this->trustStores = collect( [
            new LinuxTrustStore( '/etc/pki/ca-trust/source/anchors/',
                '/etc/pki/ca-trust/source/anchors/%s.pem',
                'update-ca-trust extract' ),
            new LinuxTrustStore( '/usr/local/share/ca-certificates/',
                '/usr/local/share/ca-certificates/%s.crt',
                'update-ca-certificates' ),
            new LinuxTrustStore( '/etc/ca-certificates/trust-source/anchors/',
                '/etc/ca-certificates/trust-source/anchors/%s.crt',
                'trust extract-compat' ),
            new LinuxTrustStore( '/usr/share/pki/trust/anchors/',
                '/usr/share/pki/trust/anchors/%s.pem',
                'update-ca-certificates' ),
        ] );

        $this->filesystem = $this->mock( Filesystem::class );
        $this->runner     = $this->mock( CommandRunner::class );
        $this->pem        = storage_path( 'tests/tribeCa.pem' );
    }

    public function test_it_detects_installed_ca_certificate() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/' )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/local/share/ca-certificates/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/ca-certificates/trust-source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/share/pki/trust/anchors/' )
                         ->andReturnFalse();

        $linuxTrust = $this->app->make( Linux::class, [
            'trustStores' => $this->trustStores,
        ] );

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/tribeCA.pem' )
                         ->andReturnTrue();

        $this->assertTrue( $linuxTrust->installed() );
    }

    public function test_it_bypasses_unknown_operating_system_ca_certificate() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/local/share/ca-certificates/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/ca-certificates/trust-source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/share/pki/trust/anchors/' )
                         ->andReturnFalse();

        $linuxTrust = $this->app->make( Linux::class, [
            'trustStores' => $this->trustStores,
        ] );

        $this->assertTrue( $linuxTrust->installed() );
    }

    public function test_it_trust_certificates_in_redhat() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/' )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/local/share/ca-certificates/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/ca-certificates/trust-source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/share/pki/trust/anchors/' )
                         ->andReturnFalse();

        $linuxTrust = $this->app->make( Linux::class, [
            'trustStores' => $this->trustStores,
        ] );

        $this->assertSame( 'update-ca-trust extract', $linuxTrust->store()->command() );
        $this->assertSame( '/etc/pki/ca-trust/source/anchors/%s.pem', $linuxTrust->store()->filename() );

        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'command',
                         'cp',
                         '-f',
                         $this->pem,
                         $linuxTrust->getHostCa(),
                     ] )
                     ->once()
                     ->andReturnSelf();


        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'update-ca-trust',
                         'extract',
                     ] )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )
                     ->twice()
                     ->andReturnSelf();


        $linuxTrust->trustCa( $this->pem );
    }

    public function test_it_trusts_certificates_in_debian() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/local/share/ca-certificates/' )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/ca-certificates/trust-source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/share/pki/trust/anchors/' )
                         ->andReturnFalse();

        $linuxTrust = $this->app->make( Linux::class, [
            'trustStores' => $this->trustStores,
        ] );

        $this->assertSame( 'update-ca-certificates', $linuxTrust->store()->command() );
        $this->assertSame( '/usr/local/share/ca-certificates/%s.crt', $linuxTrust->store()->filename() );

        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'command',
                         'cp',
                         '-f',
                         $this->pem,
                         $linuxTrust->getHostCa(),
                     ] )
                     ->once()
                     ->andReturnSelf();


        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'update-ca-certificates',
                     ] )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )
                     ->twice()
                     ->andReturnSelf();


        $linuxTrust->trustCa( $this->pem );
    }

    public function test_it_trusts_certificates_in_arch() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/local/share/ca-certificates/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/ca-certificates/trust-source/anchors/' )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/share/pki/trust/anchors/' )
                         ->andReturnFalse();

        $linuxTrust = $this->app->make( Linux::class, [
            'trustStores' => $this->trustStores,
        ] );

        $this->assertSame( 'trust extract-compat', $linuxTrust->store()->command() );
        $this->assertSame( '/etc/ca-certificates/trust-source/anchors/%s.crt', $linuxTrust->store()->filename() );

        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'command',
                         'cp',
                         '-f',
                         $this->pem,
                         $linuxTrust->getHostCa(),
                     ] )
                     ->once()
                     ->andReturnSelf();


        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'trust',
                         'extract-compat',
                     ] )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )
                     ->twice()
                     ->andReturnSelf();


        $linuxTrust->trustCa( $this->pem );
    }

    public function test_it_trusts_certificates_in_other_linux_flavors() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/local/share/ca-certificates/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/ca-certificates/trust-source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/share/pki/trust/anchors/' )
                         ->andReturnTrue();

        $linuxTrust = $this->app->make( Linux::class, [
            'trustStores' => $this->trustStores,
        ] );

        $this->assertSame( 'update-ca-certificates', $linuxTrust->store()->command() );
        $this->assertSame( '/usr/share/pki/trust/anchors/%s.pem', $linuxTrust->store()->filename() );

        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'command',
                         'cp',
                         '-f',
                         $this->pem,
                         $linuxTrust->getHostCa(),
                     ] )
                     ->once()
                     ->andReturnSelf();


        $this->runner->shouldReceive( 'run' )
                     ->with( [
                         'sudo',
                         '-s',
                         'update-ca-certificates',
                     ] )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )
                     ->twice()
                     ->andReturnSelf();


        $linuxTrust->trustCa( $this->pem );
    }

    public function test_it_throws_exception_on_invalid_flavor() {
        $this->expectException( \RuntimeException::class );

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/pki/ca-trust/source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/local/share/ca-certificates/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/etc/ca-certificates/trust-source/anchors/' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( '/usr/share/pki/trust/anchors/' )
                         ->andReturnFalse();

        $linuxTrust = $this->app->make( Linux::class, [
            'trustStores' => $this->trustStores,
        ] );

        $linuxTrust->trustCa( $this->pem );
    }

}
