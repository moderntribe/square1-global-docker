<?php declare( strict_types=1 );

namespace App\Services\Update;

use Exception;
use App\Services\Phar;
use Filebase\Document;
use App\Services\Terminator;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Downloads and installs a newly released phar
 *
 * @package App\Services\Update
 */
class Installer {

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \App\Services\Phar
     */
    protected $phar;

    /**
     * @var \App\Services\Terminator
     */
    protected $terminator;

    /**
     * Installer constructor.
     *
     * @param  \Symfony\Component\Filesystem\Filesystem  $filesystem
     * @param  \App\Services\Phar                        $phar
     * @param  \App\Services\Terminator                  $terminator
     */
    public function __construct( Filesystem $filesystem, Phar $phar, Terminator $terminator ) {
        $this->filesystem = $filesystem;
        $this->phar       = $phar;
        $this->terminator = $terminator;
    }

    /**
     * Download a file to a temporary location
     *
     * @param  \Filebase\Document  $release    The release document
     * @param  string              $localFile  The path to the so binary
     *
     * @throws \Exception
     */
    public function download( Document $release, string $localFile ): void {
        $tempFile = $this->filesystem->tempnam( '/tmp', 'so_', '.phar' );

        $this->filesystem->copy( $release->download, $tempFile );

        $this->install( $tempFile, $localFile );

        // Always kill execution after an upgrade
        $this->terminator->exit( sprintf( 'Successfully updated to %s.', $release->version ) );
    }

    /**
     * Overwrite the existing so.phar with the newly updated version.
     *
     * @param  string  $tempFile
     * @param  string  $localFile
     *
     * @throws \Exception
     */
    protected function install( string $tempFile, string $localFile ): void {
        try {
            $this->filesystem->chmod( $tempFile, 0755 );

            $phar = $this->phar->testPhar( $tempFile );

            // Free variable to unlock the phar
            unset( $phar );

            // Overwrite the local phar
            $this->filesystem->rename( $tempFile, $localFile, true );
        } catch ( Exception $exception ) {
            $this->filesystem->remove( [ $tempFile ] );
            throw new Exception( $exception->getMessage() );
        }
    }

}
