<?php declare( strict_types=1 );

namespace App\Services\Update;

use Exception;
use App\Services\Phar;
use Filebase\Document;
use App\Commands\BaseCommand;
use LaravelZero\Framework\Commands\Command;
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
     * Installer constructor.
     *
     * @param  \Symfony\Component\Filesystem\Filesystem  $filesystem
     * @param  \App\Services\Phar                        $phar
     */
    public function __construct( Filesystem $filesystem, Phar $phar ) {
        $this->filesystem = $filesystem;
        $this->phar       = $phar;
    }

    /**
     * Download a file to a temporary location
     *
     * @param  \Filebase\Document                       $release
     * @param  string                                   $localFile  The
     *
     * @param  \LaravelZero\Framework\Commands\Command  $command
     *
     * @throws \Exception
     */
    public function download( Document $release, string $localFile, Command $command ): void {
        $tempFile = $this->filesystem->tempnam( '/tmp', 'so_', '.phar' );

        $this->filesystem->copy( $release->download, $tempFile );

        $this->install( $tempFile, $localFile );

        $command->info( sprintf( 'Successfully updated to %s.', $release->version ) );

        // Always kill execution after an upgrade
        exit ( BaseCommand::EXIT_SUCCESS );
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
