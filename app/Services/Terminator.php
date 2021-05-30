<?php declare(strict_types=1);

namespace App\Services;

/**
 * Terminates Script Execution
 *
 * @codeCoverageIgnore
 *
 * @package App\Services
 */
class Terminator {

	/**
	 * Stop execution with an exit code.
	 *
	 * @param  int  $code  0 for success, 1-255 is an error.
	 */
	public function exitWithCode( int $code = 0 ): void {
		exit( $code );
	}

	/**
	 * Stop execution and display a message.
	 *
	 * @param  string  $message
	 */
	public function exitWithMessage( string $message ): void {
		exit( $message );
	}

}
