<?php declare(strict_types=1);

namespace App\Contracts;

use LaravelZero\Framework\Commands\Command;

/**
 * Docker volume type configuration.
 *
 * @package App\Contracts
 */
interface Volume {

	/**
	 * Whether this volume type is supported for the
	 * current operating system.
	 *
	 * @return bool
	 */
	public function supported(): bool;

	/**
	 * Whether a volume type is currently enabled.
	 *
	 * @param string $volume The volume type.
	 *
	 * @return bool
	 */
	public function enabled( string $volume ): bool;

	/**
	 * The tasks to enable support for this volume type.
	 *
	 * @param  \LaravelZero\Framework\Commands\Command  $command
	 */
	public function enable( Command $command ): bool;

	/**
	 * Remove any configuration for this volume type.
	 *
	 * Should throw an exception on error.
	 */
	public function remove(): void;
}
