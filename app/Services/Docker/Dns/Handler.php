<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns;

use RuntimeException;
use LaravelZero\Framework\Commands\Command;
use App\Services\Docker\Dns\OsSupport\BaseSupport;

/**
 * DNS Handler
 *
 * @package App\Services\Docker\Dns
 */
class Handler {

    /**
     * Operating System DNS support.
     *
     * @var \App\Services\Docker\Dns\OsSupport\BaseSupport
     */
    protected $osSupport;

    /**
     * Handler constructor.
     *
     * @param  \App\Services\Docker\Dns\OsSupport\BaseSupport  $osSupport
     */
    public function __construct( BaseSupport $osSupport ) {
        $this->osSupport = $osSupport;
    }

    /**
     * Whether this Operating System has supported resolvers that are enabled.
     *
     * @return bool
     */
    public function enabled(): bool {
        $collection = $this->osSupport->resolvers();

        $enabledResolvers = $collection->map( function ( $resolver ) {
            return ! $resolver->supported() || $resolver->enabled();
        } );

        return ! $enabledResolvers->contains( function ( $hasResolver ) {
            return false === $hasResolver;
        } );
    }

    /**
     * Save the nameserver to the proper resolver file.
     *
     * @param  \LaravelZero\Framework\Commands\Command  $command
     */
    public function enable( Command $command ): void {
        if ( ! $this->osSupport->supported() ) {
            throw new RuntimeException( 'Operating system not supported' );
        }

        $collection = $this->osSupport->resolvers();

        $collection->each( function ( $resolver ) use ( $command ) {
            if ( $resolver->supported() && ! $resolver->enabled() ) {
                $resolver->enable( $command );
            }
        } );
    }

}
