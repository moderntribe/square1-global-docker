<?php declare( strict_types=1 );

namespace App\Services\Certificate;

use RuntimeException;
use App\Contracts\Runner;
use App\Contracts\Trustable;
use Illuminate\Filesystem\Filesystem;

/**
 * Class Ca
 *
 * @package App\Services\Certificate
 */
class Ca {

    /**
     * The prefix/name for certificate related files.
     */
    public const NAME = 'tribeCA';

    /**
     * The name of the CA key file
     *
     * @var string
     */
    public const KEY_NAME = self::NAME . '.key';

    /**
     * The name of the CA pem file
     */
    public const PEM_NAME = self::NAME . '.pem';

    /**
     * @var \App\Contracts\Trustable
     */
    protected $trust;

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
     * Ca constructor.
     *
     * @param  \App\Contracts\Trustable           $trust
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Contracts\Runner              $runner
     */
    public function __construct( Trustable $trust, Filesystem $filesystem, Runner $runner ) {
        $this->trust      = $trust;
        $this->filesystem = $filesystem;
        $this->runner     = $runner;
    }

    /**
     * Whether the certificate is already installed on the host system.
     *
     * @return bool
     */
    public function installed(): bool {
        return $this->trust->installed();
    }

    /**
     * Create the SquareOne CA certificate.
     *
     * @param  string  $savePath  The directory to save certificates
     * @param  int     $days      The number of days the certificate is valid for. Most browsers don't trust anything longer than 825.
     * @param  bool    $trust     Whether to automatically trust the certificate.
     *
     * @return string The path to the CA certificate.
     */
    public function create( string $savePath = '', int $days = 825, bool $trust = true ): string {
        $key = $savePath . '/' . self::KEY_NAME;
        $pem = $savePath . '/' . self::PEM_NAME;

        if ( $this->filesystem->exists( $key ) || $this->filesystem->exists( $pem ) ) {
            throw new RuntimeException( sprintf( 'The CA .key or .pem file already exists in %s. Manually delete it and run the command again', $savePath ) );
        }

        $this->runner->with( [
            'days'   => $days,
            'keyout' => $key,
            'out'    => $pem,
            'subj'   => '/C=US/ST=California/L=Santa Cruz/O=Modern Tribe/OU=Dev/CN=tri.be',
        ] )->run( 'openssl req -x509 -new -nodes -sha256 -newkey rsa:4096 -days {{ $days }} -keyout {{ $keyout }} -out {{ $out }} -subj {{ $subj }}' )
                     ->throw();

        if ( $trust ) {
            $this->trustCa( $pem );
        }

        return $pem;
    }

    /**
     * Trust a CA certificate.
     *
     * @param  string  $pem  The path to the CA certificate.
     */
    public function trustCa( string $pem ) {
        $this->trust->trustCa( $pem );
    }

}
