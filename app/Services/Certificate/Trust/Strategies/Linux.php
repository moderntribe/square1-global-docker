<?php declare( strict_types=1 );

namespace App\Services\Certificate\Trust\Strategies;

use RuntimeException;
use App\Contracts\Runner;
use App\Services\Certificate\Ca;
use Illuminate\Cache\Repository;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use App\Services\Certificate\Trust\BaseTrust;
use App\Services\Certificate\Trust\LinuxTrustStore;

/**
 * Trust Certificates for different Linux flavors.
 *
 * @package App\Services\Certificate\Trust\Strategies
 */
class Linux extends BaseTrust {

    public const CACHE_KEY = 'trust';

    /**
     * The current LinuxTrustStore instance, if available for this flavor.
     *
     * @var array|bool|mixed
     */
    protected $store;

    /**
     * Linux constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Contracts\Runner              $runner
     * @param  \Illuminate\Support\Collection     $trustStores
     * @param  \Illuminate\Cache\Repository       $cache
     */
    public function __construct( Filesystem $filesystem, Runner $runner, Collection $trustStores, Repository $cache ) {
        parent::__construct( $filesystem, $runner );

        $store = $cache->get( self::CACHE_KEY );

        if ( is_null( $store ) ) {
            // Laravel cache will not store null values, but false is ok.
            $trust = $this->getTrustStore( $trustStores );

            $store = $trust->isNotEmpty() ? $trust->first() : false;
            $cache->forever( self::CACHE_KEY, $store );
        }

        $this->store = $store;
    }

    /**
     * Get the current trust store for this flavor.
     *
     * @return \App\Services\Certificate\Trust\LinuxTrustStore|null
     */
    public function store(): ?LinuxTrustStore {
        return ! empty( $this->store ) ? $this->store : null;
    }

    /**
     * Whether the CA certificate is installed.
     *
     * @return bool
     */
    public function installed(): bool {
        $path = $this->getHostCa();

        // There is no supported trust store for this Linux flavor.
        if ( empty( $path ) ) {
            return true;
        }

        return (bool) $this->filesystem->exists( $this->getHostCa() );
    }

    /**
     * Run the commands in order to trust a CA certificate.
     *
     * @param  string  $crt  The path to the created crt file.
     */
    public function trustCa( string $crt ): void {
        if ( empty( $this->store ) ) {
            throw new RuntimeException( sprintf( 'Operating system not supported. Please manually install %s', $crt ) );
        }

        $this->install( $crt, $this->getHostCa() );

        $command = explode( ' ', $this->store->command() );
        $command = array_merge( [ 'sudo', '-s' ], $command );

        $this->runner->run( $command )->throw();
    }

    /**
     * Linux doesn't require additional trusting.
     *
     * @codeCoverageIgnore
     *
     * @param  string  $crt
     *
     * @return mixed|void
     */
    public function trustCertificate( string $crt ) {
        return;
    }

    /**
     * Get the trust store for this Linux flavor.
     *
     * @param  \Illuminate\Support\Collection  $trustStores
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getTrustStore( Collection $trustStores ): Collection {
        /** @var \App\Services\Certificate\Trust\LinuxTrustStore $trust */
        return $trustStores->filter( function ( $store ) {
            return $this->filesystem->exists( $store->directory() );
        } );
    }

    /**
     * Get the host CA certificate path.
     *
     * All LinuxStoreTrust's paths should have a %s.crt, %s.pem etc to replace.
     *
     * @return string
     */
    public function getHostCa(): string {
        return ! empty( $this->store ) ? sprintf( $this->store->filename(), Ca::NAME ) : '';
    }

    /**
     * Install the Linux CA Certificate.
     *
     * @param  string  $from  The CA in ~/.config/squareone/global/certs.
     * @param  string  $to    The LinuxTrustStore's file path.
     */
    protected function install( string $from, string $to ) {
        $this->runner->run( [
            'sudo',
            '-s',
            'command',
            'cp',
            '-f',
            $from,
            $to,
        ] )->throw();
    }

}
