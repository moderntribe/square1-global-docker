<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\OsSupport;

/**
 * Class MacOs
 *
 * @package App\Services\Docker\Dns\OsSupport
 */
class MacOs extends BaseSupport {

	/**
	 * MacOS is supported.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return true;
	}

}
