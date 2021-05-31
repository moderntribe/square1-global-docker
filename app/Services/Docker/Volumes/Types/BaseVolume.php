<?php declare(strict_types=1);

namespace App\Services\Docker\Volumes\Types;

use App\Contracts\Volume;
use App\Services\OperatingSystem;
use App\Services\Settings\Groups\AllSettings;

/**
 * Base Docker Volume.
 *
 * @package App\Services\Docker\Dns\Resolvers
 */
abstract class BaseVolume implements Volume {

	protected OperatingSystem $os;
	protected AllSettings $settings;

	/**
	 * BaseVolume constructor.
	 *
	 * @param \App\Services\OperatingSystem             $os
	 * @param \App\Services\Settings\Groups\AllSettings $settings
	 */
	public function __construct( OperatingSystem $os, AllSettings $settings ) {
		$this->os       = $os;
		$this->settings = $settings;
	}

	/**
	 * Whether a volume type is currently enabled.
	 *
	 * @param string $volume
	 *
	 * @return bool
	 */
	public function enabled( string $volume ): bool {
		return $this->settings->docker->volume === $volume;
	}

}
