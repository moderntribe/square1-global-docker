<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\Resolvers;

use App\Contracts\Runner;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

/**
 * Set resolvers for systems using the resolvconf package.
 *
 * @see     https://packages.ubuntu.com/focal/resolvconf
 *
 * @package App\Services\Docker\Dns\Resolvers
 */
class ResolvConf extends BaseResolver {

	/**
	 * The path to the custom resolv file dependant on the operating system in use.
	 *
	 * e.g. /etc/resolvconf/resolv.conf.d/head
	 */
	protected string $file;

	/**
	 * ResolvConf constructor.
	 *
	 * @param   \App\Contracts\Runner              $runner
	 * @param   \Illuminate\Filesystem\Filesystem  $filesystem
	 * @param   string                             $file
	 */
	public function __construct( Runner $runner, Filesystem $filesystem, string $file ) {
		parent::__construct( $runner, $filesystem );

		$this->file = $file;
	}

	/**
	 * Check if resolvconf is active.
	 *
	 * @see https://packages.ubuntu.com/focal/resolvconf
	 *
	 * @return bool
	 */
	public function supported(): bool {
		$response = $this->runner->run( 'systemctl status resolvconf' );

		if ( $response->ok() ) {
			if ( str_contains( (string) $response, 'active (exited)' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the relevant os resolv file contains the correct nameservers.
	 *
	 * @return bool
	 */
	public function enabled(): bool {
		try {
			$content = $this->filesystem->get( $this->file );

			if ( str_contains( $content, 'nameserver 127.0.0.1' ) ) {
				return true;
			}
		}
		catch ( FileNotFoundException $exception ) {
			return false;
		}

		return false;
	}

	/**
	 * Enable local nameservers for /etc/resolv.conf.
	 *
	 * @param   \LaravelZero\Framework\Commands\Command  $command
	 */
	public function enable( Command $command ): void {
		$command->task( sprintf( '<comment>➜ Adding 127.0.0.1 nameservers to %s</comment>', $this->file ), call_user_func( [
			$this,
			'addNameservers',
		] ) );
	}

	/**
	 * Add nameservers to the appropriate resolvconf head file.
	 *
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	public function addNameservers(): void {
		$this->checkDirectory();
		$this->runner->run( 'echo "nameserver 127.0.0.1" | sudo tee ' . $this->file )->throw();
	}

	/**
	 * Create the resolver directory if it doesn't exist.
	 *
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	protected function checkDirectory(): void {
		$directory = $this->filesystem->dirname( $this->file );

		if ( $this->filesystem->exists( $directory ) ) {
			return;
		}

		$this->runner->with( [
			'directory' => $directory,
		] )->run( 'sudo mkdir -p {{ $directory }}' )->throw();
	}

}
