<?php declare(strict_types=1);

namespace App\Services\Nfs;

use App\Contracts\Runner;
use App\Exceptions\SystemExitException;
use Illuminate\Filesystem\Filesystem;
use Throwable;

/**
 * Validate NFS is properly configured.
 *
 * @package App\Services\Nfs
 */
class NfsValidator {

	public const NFS_CONF = '/etc/nfs.conf';

	/**
	 * The Illuminate Filesystem.
	 */
	protected Filesystem $filesystem;

	/**
	 * The command runner.
	 */
	protected Runner $runner;

	/**
	 * Controls the nfsd binary.
	 */
	protected Nfsd $nfsd;

	/**
	 * Whether we should restart nfsd.
	 */
	protected bool $restart = false;

	/**
	 * Validator constructor.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem  $filesystem
	 * @param  \App\Contracts\Runner              $runner
	 * @param  \App\Services\Nfs\Nfsd             $nfsd
	 */
	public function __construct( Filesystem $filesystem, Runner $runner, Nfsd $nfsd ) {
		$this->filesystem = $filesystem;
		$this->runner     = $runner;
		$this->nfsd       = $nfsd;
	}

	/**
	 * See if we should restart the nfsd server.
	 */
	public function __destruct() {
		if ( ! $this->restart ) {
			return;
		}

		$this->nfsd->restart();

		sleep( 5 );
	}

	/**
	 * Validate NFS Export Tables are configured correctly.
	 *
	 * @param  string  $exportsFilePath
	 *
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	public function validate( string $exportsFilePath ): void {
		$this->verifyExportsFileExists( $exportsFilePath )
			 ->verifyNfsReservedPort();
	}

	/**
	 * Test the exports table on a temporary file. The thrown exception should contain
	 * the errors required to fix to continue.
	 *
	 * @param  string  $tempFile  The path to the temporary /etc/exports file to validate.
	 *
	 * @throws \App\Exceptions\SystemExitException
	 */
	public function checkExports( string $tempFile ): void {
		try {
			$this->nfsd->check( $tempFile );
		} catch ( Throwable $exception ) {
			$this->filesystem->delete( $tempFile );

			throw new SystemExitException( 'There was an error in the /etc/exports file when trying to set up the NFS share: ' . $exception->getMessage() );
		}
	}

	/**
	 * nfsd will not start without a config file. Creating the file triggers a nfsd start
	 * so give it time to start up.
	 *
	 * @param  string  $exportsFilePath
	 *
	 * @return \App\Services\Nfs\NfsValidator
	 *
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	protected function verifyExportsFileExists( string $exportsFilePath ): NfsValidator {
		if ( $this->filesystem->exists( $exportsFilePath ) ) {
			return $this;
		}

		$this->runner->with( [ 'path' => $exportsFilePath, ] )
					 ->run( 'sudo touch {{ $path }}' )
					 ->throw();

		sleep( 5 );

		return $this;
	}

	/**
	 * Ensure the NFS server has nfs.server.mount.require_resv_port = 0
	 * otherwise NFS mounts usually fail.
	 *
	 * @return \App\Services\Nfs\NfsValidator
	 *
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	protected function verifyNfsReservedPort(): NfsValidator {
		$line    = 'nfs.server.mount.require_resv_port = 0';
		$content = $this->filesystem->get( self::NFS_CONF );

		if ( ! str_contains( $content, $line ) ) {
			$this->runner->with( [ 'content' => $line, 'path' => self::NFS_CONF ] )
						 ->run( 'echo {{ $content }} | sudo tee -a {{ $path }}' )
						 ->throw();
			$this->restart = true;
		}

		return $this;
	}

}
