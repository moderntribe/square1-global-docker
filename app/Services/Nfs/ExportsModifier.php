<?php declare(strict_types=1);

namespace App\Services\Nfs;

use App\Contracts\Runner;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Modifies a the NFS /etc/exports file.
 *
 * @package App\Services\Nfs
 */
class ExportsModifier {

	public const EXPORTS_FILE_PATH = '/etc/exports';
	public const OPENING_TAG       = '#SquareOneStart';
	public const CLOSING_TAG       = '#SquareOneEnd';

	/**
	 * The Symfony filesystem.
	 */
	protected Filesystem $filesystem;

	/**
	 * The NFS validator.
	 */
	protected NfsValidator $validator;

	/**
	 * The command runner.
	 */
	protected Runner $runner;

	/**
	 * ExportsModifier constructor.
	 *
	 * @param  \Symfony\Component\Filesystem\Filesystem  $filesystem
	 * @param  \App\Services\Nfs\NfsValidator            $validator
	 * @param  \App\Contracts\Runner                     $runner
	 */
	public function __construct( Filesystem $filesystem, NfsValidator $validator, Runner $runner ) {
		$this->filesystem = $filesystem;
		$this->validator  = $validator;
		$this->runner     = $runner;
	}

	/**
	 * Add SquareOne NFS share to /etc/exports.
	 *
	 * @param  string  $directory The directory to share.
	 * @param  string  $gatewayIP The docker gateway IP address.
	 * @param  int     $uid The current user's ID.
	 * @param  int     $gid The current user's group ID.
	 *
	 * @throws \App\Exceptions\SystemExitException
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function add( string $directory, string $gatewayIP, int $uid, int $gid ): void {
		$this->validator->validate( self::EXPORTS_FILE_PATH );

		$original = file_get_contents( self::EXPORTS_FILE_PATH );
		$exports  = $this->getCleanedExports( $original );
		$exports .= sprintf( "%s\n", self::OPENING_TAG );
		$exports .= sprintf( "%s %s -alldirs -maproot=%d:%d localhost\n", $directory, $gatewayIP, $uid, $gid );
		$exports .= sprintf( "%s\n", self::CLOSING_TAG );

		// No changes to the configuration
		if ( $exports === $original ) {
			return;
		}

		$this->write( $exports );
	}

	/**
	 * Remove the network shares from /etc/exports.
	 *
	 * @throws \App\Exceptions\SystemExitException
	 */
	public function remove(): void {
		$original = file_get_contents( self::EXPORTS_FILE_PATH );
		$exports  = $this->getCleanedExports( $original );

		// No changes to the configuration
		if ( $exports === $original ) {
			return;
		}

		$this->write( $exports );
	}

	/**
	 * Remove any existing SquareOne configuration from /etc/exports.
	 *
	 * @param  string  $contents The contents of /etc/exports
	 *
	 * @return string
	 */
	protected function getCleanedExports( string $contents = '' ): string {
		$contents = $contents ?: file_get_contents( self::EXPORTS_FILE_PATH );
		$regex    = sprintf( "/%s[\s\S]+?%s/", self::OPENING_TAG, self::CLOSING_TAG );

		return preg_replace( $regex, '', $contents );
	}

	/**
	 * Write configuration to the /etc/exports file.
	 *
	 * @param  string  $content  The content to write to /etc/exports
	 *
	 * @throws \App\Exceptions\SystemExitException
	 */
	protected function write( string $content ): void {
		$tmpFile = $this->filesystem->tempnam( '/tmp', 'exports' );
		$this->filesystem->dumpFile( $tmpFile, $content );
		$this->validator->checkExports( $tmpFile );
		$this->filesystem->remove( $tmpFile );

		// If we made it this far without thrown exceptions, write the exports configuration.
		$this->runner->with( [ 'path' => self::EXPORTS_FILE_PATH, 'content' => $content ] )
					 ->run( 'echo {{ $content }} | sudo tee /etc/exports' )
					 ->throw();
	}

}
