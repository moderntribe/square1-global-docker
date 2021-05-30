<?php declare(strict_types=1);

namespace App\Services\Config;

use Illuminate\Filesystem\Filesystem;

/**
 * Extend to modify SquareOne default configurations.
 *
 * @package App\Services\Config
 */
abstract class BaseConfig {

	/**
	 * Filesystem.
	 */
	protected Filesystem $filesystem;

	/**
	 * The SquareOne configuration directory.
	 */
	protected string $directory;

	/**
	 * Github constructor.
	 *
	 * @param \Illuminate\Filesystem\Filesystem $filesystem
	 * @param  string      $directory
	 */
	public function __construct( Filesystem $filesystem, string $directory ) {
		$this->filesystem = $filesystem;
		$this->directory  = $directory . '/defaults';
	}

}
