<?php declare(strict_types=1);

namespace App\Services\Update;

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

	protected Filesystem $filesystem;
	protected Phar $phar;
	protected Terminator $terminator;

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
	 * Download a file to a temporary location.
	 *
	 * @param  \Filebase\Document                       $release    The release document
	 * @param  string                                   $localFile  The path to the so binary
	 * @param  \LaravelZero\Framework\Commands\Command  $command    The command instance
	 *
	 * @throws \Exception
	 */
	public function download( Document $release, string $localFile, Command $command ): void {
		$tempFile = $this->filesystem->tempnam( '/tmp', 'so_', '.phar' );

		$this->filesystem->copy( $release->download, $tempFile );

		$this->install( $tempFile, $localFile );

		$command->info( sprintf( 'Successfully updated to %s.', $release->version ) );

		// Always kill execution after an upgrade
		$this->terminator->exitWithCode();
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
		} catch ( \Throwable $exception ) {
			$this->filesystem->remove( [ $tempFile ] );

			throw new Exception( $exception->getMessage() );
		}
	}

}
