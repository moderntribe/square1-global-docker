<?php declare( strict_types=1 );

namespace App\Services\Certificate;

use App\Contracts\Runner;
use Illuminate\Filesystem\Filesystem;

/**
 * SSL Certificate Model
 *
 * @package App\Services
 */
class Certificate {

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * Certificate constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Contracts\Runner              $runner
     */
    public function __construct( Filesystem $filesystem, Runner $runner ) {
        $this->filesystem = $filesystem;
        $this->runner     = $runner;
    }

    /**
     * Check if a certificate exists.
     *
     * @param  string  $certFile  The full path to the certificate
     *
     * @return bool
     */
    public function exists( string $certFile ): bool {
        return ( ! empty( $certFile ) && is_file( $certFile ) );
    }

    /**
     * Check if a certificate is expired.
     *
     * @param  string  $certFile  The full path to the certificate
     *
     * @return bool
     */
    public function expired( string $certFile ): bool {
        $info = openssl_x509_parse( file_get_contents( $certFile ) );

        return ( ! empty( $info['validTo_time_t'] ) && $info['validTo_time_t'] < time() );
    }

    /**
     * Create a local certificate.
     *
     * @param  string  $domain         The domain name, no www.
     * @param  string  $certDirectory  The directory where certificates are stored.
     * @param  int     $days           The amount of days for the certificate to be valid.
     */
    public function create( string $domain, string $certDirectory, int $days = 825 ): void {
        $this->runner->with( [
            'domain' => $domain,
            'key'    => "{$certDirectory}/{$domain}.key",
            'csr'    => "{$certDirectory}/{$domain}.csr",
            'subj'   => "/C=US/ST=California/L=Santa Cruz/O=Modern Tribe/OU=Dev/CN=${domain}",
        ] )->run( 'openssl req -new -nodes -sha256 -newkey rsa:4096 -keyout {{ $key }} -out {{ $csr }} -subj {{ $subj }}' )
                     ->throw();

        $ext = "{$certDirectory}/{$domain}.ext";

        $this->createExt( $ext, $domain );

        $this->runner->with( [
            'days'     => $days,
            'csr'      => "{$certDirectory}/{$domain}.csr",
            'ca'       => "{$certDirectory}/" . Ca::PEM_NAME,
            'ca_key'   => "{$certDirectory}/" . Ca::KEY_NAME,
            'ext_file' => $ext,
            'out'      => "{$certDirectory}/{$domain}.crt",
        ] )
                     ->run( 'openssl x509 -req -days {{ $days }} -sha256 -in {{ $csr }} -CA {{ $ca }} -CAkey {{ $ca_key }} -CAcreateserial -extfile {{ $ext_file }} -out {{ $out }}' )
                     ->throw();

        $this->filesystem->delete( $ext );
    }

    /**
     * Write an .ext file.
     *
     * @param  string  $file    The full path to the .ext file.
     * @param  string  $domain  The domain name.
     *
     * @return bool
     */
    protected function createExt( string $file, string $domain ): bool {
        $testDomain = $this->getTestDomain( $domain );

        $content = 'authorityKeyIdentifier=keyid,issuer' . PHP_EOL;
        $content .= 'basicConstraints=CA:FALSE' . PHP_EOL;
        $content .= 'keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment' . PHP_EOL;
        $content .= 'subjectAltName = @alt_names' . PHP_EOL;
        $content .= '[alt_names]' . PHP_EOL;
        $content .= "DNS.1 = {$domain}" . PHP_EOL;
        $content .= "DNS.2 = *.{$domain}" . PHP_EOL;
        $content .= "DNS.3 = {$testDomain}" . PHP_EOL;
        $content .= "DNS.4 = *.{$testDomain}" . PHP_EOL;

        return (bool) $this->filesystem->put( $file, $content );
    }

    /**
     * Build a test domain from the main domain.
     *
     * @param  string  $domain  The domain
     *
     * @return string The domain with "test" injected before the TLD.
     */
    protected function getTestDomain( string $domain ): string {
        return str_replace( '.tribe', 'test.tribe', $domain );
    }

}
