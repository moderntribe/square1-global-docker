<?php declare(strict_types=1);

namespace App\Services;

use App\Contracts\File;

/**
 * Validate Xdebug is correctly configured for a project.
 *
 * @package App\Services
 */
class XdebugValidator {

	protected File $file;

	/**
	 * XdebugValidator constructor.
	 *
	 * @param  \App\Contracts\File  $file
	 */
	public function __construct( File $file ) {
		$this->file = $file;
	}

	/**
	 * Determine if a php.ini file contains xdebug v3.0+ configuration.
	 *
	 * @param  string  $phpIni The path to the project's php-ini-overrides.ini
	 *
	 * @return bool
	 */
	public function valid( string $phpIni ): bool {
		if ( ! $this->file->exists( $phpIni ) ) {
			return true;
		}

		return $this->file->contains( $phpIni, 'xdebug.mode' );
	}

}
