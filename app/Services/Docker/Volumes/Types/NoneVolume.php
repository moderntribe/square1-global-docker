<?php declare(strict_types=1);

namespace App\Services\Docker\Volumes\Types;

use LaravelZero\Framework\Commands\Command;

/**
 * No Docker Volume.
 *
 * @package App\Services\Docker\Volumes
 */
class NoneVolume extends BaseVolume {

	/**
	 * Supported on every OS.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return true;
	}

	/**
	 * No configuration required to enable this volume type.
	 *
	 * @param \LaravelZero\Framework\Commands\Command $command
	 *
	 * @return bool
	 */
	public function enable( Command $command ): bool {
		return true;
	}

	/**
	 * Nothing to remove.
	 */
	public function remove(): void {
	}

}
