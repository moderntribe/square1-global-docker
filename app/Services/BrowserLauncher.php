<?php declare(strict_types=1);

namespace App\Services;

use App\Contracts\Runner;
use RuntimeException;

/**
 * Class BrowserLauncher
 *
 * @package App\Services
 */
class BrowserLauncher {

	/**
	 * The command runner.
	 */
	protected Runner $runner;

	/**
	 * OpenUrl constructor.
	 *
	 * @param  \App\Contracts\Runner  $runner
	 */
	public function __construct( Runner $runner ) {
		$this->runner = $runner;
	}

	/**
	 * Open a URL in the user's default browser.
	 *
	 * @param  string  $url
	 */
	public function open( string $url ): void {
		$command = $this->getCommand();

		if ( empty( $command ) ) {
			throw new RuntimeException( 'Unable to find xdg-open, open or start executables', 1 );
		}

		$this->runner->run( sprintf( $command, $url ) );
	}

	/**
	 * Get the open command for the currently running operating system.
	 *
	 * @return string The open command.
	 */
	protected function getCommand(): string {
		if ( defined( 'PHP_WINDOWS_VERSION_MAJOR' ) ) {
			return 'start "web" explorer "%s"';
		}

		$linux = $this->runner->run( 'which xdg-open' );
		$osx   = $this->runner->run( 'which open' );

		if ( $linux->successful() ) {
			return 'xdg-open %s';
		}

		if ( $osx->successful() ) {
			return 'open %s';
		}

		return '';
	}

}
