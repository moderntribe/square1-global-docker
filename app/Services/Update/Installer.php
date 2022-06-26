<?php declare(strict_types=1);

namespace App\Services\Update;

use App\Services\Migrations\MigrationChecker;
use App\Services\Phar;
use App\Services\Terminator;
use Exception;
use Filebase\Document;
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
     * @var \App\Services\Terminator
     */
    protected $terminator;

    /**
     * @var \App\Services\Migrations\MigrationChecker
     */
    protected $migrationChecker;

    /**
     * Installer constructor.
     *
     * @param  \Symfony\Component\Filesystem\Filesystem   $filesystem
     * @param  \App\Services\Phar                         $phar
     * @param  \App\Services\Terminator                   $terminator
     * @param  \App\Services\Migrations\MigrationChecker  $migrationChecker
     */
    public function __construct( Filesystem $filesystem, Phar $phar, Terminator $terminator, MigrationChecker $migrationChecker ) {
        $this->filesystem       = $filesystem;
        $this->phar             = $phar;
        $this->terminator       = $terminator;
        $this->migrationChecker = $migrationChecker;
    }

    /**
     * Download a file to a temporary location.
     *
     * @param  \Filebase\Document                       $release    The release document
     * @param  string                                   $localFile  The path to the so binary
     * @param  \LaravelZero\Framework\Commands\Command  $command    The command instance
     *
     * @throws \Exception
     */
    public function download( Document $release, string $localFile, Command $command ): void {
        $tempFile = $this->filesystem->tempnam( '/tmp', 'tribe_', '.phar' );

        $this->filesystem->copy( $release->download, $tempFile );

        $this->install( $tempFile, $localFile );

        $command->info( sprintf( 'Successfully updated to %s.', $release->version ) );

        $this->migrationChecker->clear();

        // Always kill execution after an upgrade
        $this->terminator->exitWithCode();
    }

    /**
     * Overwrite the existing tribe.phar with the newly updated version.
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
