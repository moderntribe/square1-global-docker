<?php declare(strict_types=1);

namespace App\Services;

/**
 * Adapter for the Phar class to make testing easier.
 *
 * @package App\Services
 */
class Phar {

	/**
	 * Determine if something is running inside a phar.
	 *
	 * @return bool
	 */
	public function isPhar(): bool {
		return (bool) \Phar::running();
	}

	/**
	 * Test a phar works.
	 *
	 * @param  string  $file  The path to the phar.
	 *
	 * @return \Phar
	 */
	public function testPhar( string $file ): \Phar {
		return new \Phar( $file );
	}

}
