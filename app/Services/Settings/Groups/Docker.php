<?php declare(strict_types=1);

namespace App\Services\Settings\Groups;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

/**
 * Docker specific settings.
 *
 * @package App\Services\Settings\Groups
 */
class Docker extends FlexibleDataTransferObject {

	public const BIND    = 'bind';
	public const MUTAGEN = 'mutagen';
	public const NFS     = 'nfs';
	public const NONE    = 'none';

	/**
	 * The user's current docker volume type.
	 */
	public string $volume = self::BIND;

	/**
	 * Globally enabled xdebug for SquareOne projects.
	 */
	public bool $xdebug = false;

}
