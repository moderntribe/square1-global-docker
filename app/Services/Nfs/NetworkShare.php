<?php declare( strict_types=1 );

namespace App\Services\Nfs;

use App\Contracts\Runner;
use App\Services\Docker\Network;
use App\Exceptions\SystemExitException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Manage NFS shares.
 *
 * @package App\Services\Nfs
 */
class NetworkShare {

    public const EXPORTS_FILE_PATH = '/etc/exports';

    /**
     * The docker network instance.
     *
     * @var \App\Services\Docker\Network
     */
    protected $network;

    /**
     * The Symfony filesystem.
     *
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * The NFS validator.
     *
     * @var \App\Services\Nfs\NfsValidator
     */
    protected $validator;

    /**
     * Controls the nfsd binary.
     *
     * @var \App\Services\Nfs\Nfsd
     */
    protected $nfsd;

    /**
     * NFS Share constructor.
     *
     * @param  \App\Services\Docker\Network              $network
     * @param  \Symfony\Component\Filesystem\Filesystem  $filesystem
     * @param  \App\Contracts\Runner                     $runner
     * @param  \App\Services\Nfs\NfsValidator            $validator
     * @param  \App\Services\Nfs\Nfsd                    $nfsd
     */
    public function __construct( Network $network, Filesystem $filesystem, Runner $runner, NfsValidator $validator, Nfsd $nfsd ) {
        $this->network    = $network;
        $this->filesystem = $filesystem;
        $this->runner     = $runner;
        $this->validator  = $validator;
        $this->nfsd       = $nfsd;
    }

    /**
     * Adds an NFS share.
     *
     * @param  string  $directory The directory to share.
     *
     * @throws \App\Exceptions\SystemExitException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function add( string $directory ): void {

        if ( ! $this->filesystem->exists( $directory ) ) {
            throw new SystemExitException( sprintf( 'Unable to find path: %s', $directory ) );
        }

        $this->validator->validate( self::EXPORTS_FILE_PATH );

        // @TODO break this up and move elsewhere to make it testable.
        $open    = '#SquareOneStart';
        $close   = '#SquareOneEnd';
        $regex   = "/${open}[\s\S]+?${close}/";
        $exports = $existing_exports = file_get_contents( self::EXPORTS_FILE_PATH );
        $exports = preg_replace( $regex, '', $exports );

        $exports .= "${open}\n";
        $exports .= sprintf( "%s %s -alldirs -maproot=0:0\n", $directory, $this->network->getGateWayIP() );
        $exports .= "${close}\n";

        // No changes to the configuration
        if ( $exports === $existing_exports ) {
            return;
        }

        $tmpFile = $this->filesystem->tempnam( '/tmp', 'exports' );

        $this->filesystem->dumpFile( $tmpFile, $exports );
        $this->validator->checkExports( $tmpFile );
        $this->filesystem->remove( $tmpFile );

        // If we made it this far without thrown exceptions, write the exports configuration.
        $this->runner->with( [ 'path' => self::EXPORTS_FILE_PATH, 'content' => $exports ] )
                     ->run( 'echo {{ $content }} | sudo tee /etc/exports' )
                     ->throw();

        $this->nfsd->restart();
    }

    /**
     * @TODO add remove share functionality.
     *
     * @param  string  $directory
     */
    public function remove( string $directory ): void {

    }

}
