<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\OsSupport;

/**
 * Class NullOs
 *
 * @package App\Services\Docker\Dns\OsSupport
 */
class NullOs extends BaseSupport {

	/**
	 * Unknown operating system is not supported.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return false;
	}

}
