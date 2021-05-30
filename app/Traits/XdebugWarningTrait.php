<?php declare(strict_types=1);

namespace App\Traits;

trait XdebugWarningTrait {

	/**
	 * Display a warning in a Command if a user has an old configuration for Xdebug.
	 *
	 * @param  string  $phpIni The path to the project's docker php-ini-overrides.ini
	 */
	protected function outdatedXdebugWarning( string $phpIni ): void {
		$this->warn( sprintf(
			'This project\'s %s is not configured correctly for xdebug v3.0+. See %s upgrade instructions.',
			$phpIni,
			'https://github.com/moderntribe/square-one/pull/695'
		) );
	}

}
