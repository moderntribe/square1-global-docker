<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\OsSupport;

/**
 * Class Linux
 *
 * @package App\Services\Docker\Dns\OsSupport
 */
class Linux extends BaseSupport {

	/**
	 * Linux is supported.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return true;
	}

}
