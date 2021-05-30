<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\Resolvers;

/**
 * Set resolvers for MacOS
 *
 * @see     https://ss64.com/osx/scutil.html
 *
 * @package App\Services\Docker\Dns\Resolvers
 */
class Scutil extends ResolvConf {

	/**
	 * Check if this OS supports scutil, which runs on MacOS.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		$response = $this->runner->run( 'scutil --dns' );

		return $response->ok();
	}

}
