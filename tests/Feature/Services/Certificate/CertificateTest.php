<?php

namespace Tests\Feature\Services\Certificate;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Services\Certificate\Ca;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use App\Services\Certificate\Certificate;
use App\Services\Certificate\Trust\LinuxTrustStore;
use App\Services\Certificate\Trust\Strategies\Linux;

class CertificateTest extends TestCase {

    protected $certFile;
    protected $cert;
    protected $filesystem;
    protected $runner;

    protected function setUp(): void {
        parent::setUp();

        Storage::put( 'tests/certs/test.crt', $this->getTestCert() );
        $this->certFile   = storage_path( 'tests/certs/test.crt' );
        $this->filesystem = new Filesystem();
        $this->runner     = new CommandRunner();
        $this->cert       = new Certificate( $this->filesystem, $this->runner );
    }

    public function test_cert_exists() {
        $this->assertTrue( $this->cert->exists( $this->certFile ) );
    }

    public function test_cert_is_not_expired() {
        $this->assertFalse( $this->cert->expired( $this->certFile ) );
    }

    public function test_cert_does_not_exist() {
        $this->assertFalse( $this->cert->exists( storage_path( 'tests/doesnotexist.crt' ) ) );
    }

    public function test_cert_is_expired() {
        Storage::put( 'tests/certs/expired.crt', $this->getExpiredCert() );
        $file = storage_path( 'tests/certs/expired.crt' );
        $this->assertTrue( $this->cert->expired( $file ) );
    }

    public function test_it_creates_certificate() {
        $certDirectory = storage_path( 'tests/certs' );

        $trustStores = collect( [
            new LinuxTrustStore( storage_path( 'tests/truststores' ), storage_path( 'tests/truststores/%s.crt' ), 'null' ),
        ] );

        // Create CA certificate
        $linux = $this->app->make( Linux::class, [
            'trustStores' => $trustStores,
        ] );

        $ca    = new Ca( $linux, $this->filesystem, $this->runner );
        $pem   = $ca->create( $certDirectory, 10, false );

        $this->assertFileExists( $pem );
        $this->assertStringNotEqualsFile( $pem, '' );
        $this->assertFileExists( storage_path( 'tests/certs/' . Ca::KEY_NAME ) );

        // Create the local certificate
        $domain = 'test.tribe';
        $file   = storage_path( "tests/certs/{$domain}.crt" );
        $this->assertFalse( $this->cert->exists( $file ) );
        $this->cert->create( $domain, $certDirectory );

        $this->assertFileExists( $file );

    }

    /**
     * A certificate that expires way in the future
     *
     * @return string
     */
    private function getTestCert(): string {
        return trim( '
-----BEGIN CERTIFICATE-----
MIICaDCCAdGgAwIBAgIBADANBgkqhkiG9w0BAQ0FADBQMQswCQYDVQQGEwJ1czEL
MAkGA1UECAwCQ0ExGjAYBgNVBAoMEU1vZGVybiBUcmliZSBJbmMuMRgwFgYDVQQD
DA9zcXVhcmVvbmUudHJpYmUwIBcNMjAwNTEyMDUxOTA3WhgPMjI5NDAyMjUwNTE5
MDdaMFAxCzAJBgNVBAYTAnVzMQswCQYDVQQIDAJDQTEaMBgGA1UECgwRTW9kZXJu
IFRyaWJlIEluYy4xGDAWBgNVBAMMD3NxdWFyZW9uZS50cmliZTCBnzANBgkqhkiG
9w0BAQEFAAOBjQAwgYkCgYEAvtTcj9OeVDv7vsvyyHcyjgn56bQmW+duZbBbFv7W
DWlIULpOe9zLyIBpXbyzHAbSmFSsvTmMmde2OT9b+29/L3+rUqnC6dkl/kUalQOx
JaqE66jorY+JaCbVwWoMM0rnGGULC7AfHjLxHrupBUN3BDIeiRxsvQ3uMGHS/2mC
Y78CAwEAAaNQME4wHQYDVR0OBBYEFEf8A5I5aSDJKAX5oeE5JU31U4WbMB8GA1Ud
IwQYMBaAFEf8A5I5aSDJKAX5oeE5JU31U4WbMAwGA1UdEwQFMAMBAf8wDQYJKoZI
hvcNAQENBQADgYEAbZA/2rRlcOZX8b963VvFD9gEHOGli4UC4zk7WQNccxw+eAwZ
R1SuX2xj9ncBEZbheoNf7q2A1JkD1RjwIRrXqoHv7s3lbPPr0guIeYs+qAvEV4pH
VkvjhOY5bQgmhyHrCCWEbZsrCZl4+VdACXjPQ2DNPsY64eUktSpTrvyV9W8=
-----END CERTIFICATE-----' );
    }

    /**
     * An expired certificate
     *
     * @return string
     */
    private function getExpiredCert(): string {
        return trim( '
        -----BEGIN CERTIFICATE-----
MIICZjCCAc+gAwIBAgIBADANBgkqhkiG9w0BAQ0FADBQMQswCQYDVQQGEwJ1czEL
MAkGA1UECAwCQ0ExGjAYBgNVBAoMEU1vZGVybiBUcmliZSBJbmMuMRgwFgYDVQQD
DA9zcXVhcmVvbmUudHJpYmUwHhcNMjAwNTEyMDUxNzM5WhcNMTkwNTEzMDUxNzM5
WjBQMQswCQYDVQQGEwJ1czELMAkGA1UECAwCQ0ExGjAYBgNVBAoMEU1vZGVybiBU
cmliZSBJbmMuMRgwFgYDVQQDDA9zcXVhcmVvbmUudHJpYmUwgZ8wDQYJKoZIhvcN
AQEBBQADgY0AMIGJAoGBAMgcyKOiKAT/u6fRqtn2n+ZEZxdsgJ/A949bdnOjvQ+/
1HYaWlZjzSxFT/0hhoTmZhSlod5WLqY0zcf51Pgvx+QY6kKvy2HafCa3/zz3snid
eeOoKJZZMaqplYngsSOqnsJrRxiExKGXQe15mRJkJ+ZCvvVMEIQkJsNQPwol7vH7
AgMBAAGjUDBOMB0GA1UdDgQWBBSftI6gtkFQ5hI9QSWRags6lM1+yzAfBgNVHSME
GDAWgBSftI6gtkFQ5hI9QSWRags6lM1+yzAMBgNVHRMEBTADAQH/MA0GCSqGSIb3
DQEBDQUAA4GBAI1BObSWQNTwrrCGC1huF7oXul+Ec1+5fc4VWqGcAqa1y/6rGIAy
AokvrYuAkTNZ4AaDCutCoAaR3qUDYPLTVlf4e5FXlWt4L8UECwFWu46R5lFmGZOQ
lVy7U6nm10xVxW5RJgw2flq2PKKARX0Ynj/RXcuzLseghch8RTBmcuPw
-----END CERTIFICATE-----' );
    }

}
