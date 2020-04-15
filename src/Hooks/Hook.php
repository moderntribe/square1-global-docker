<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Hooks;

use Tribe\SquareOne\Models\OperatingSystem;

/**
 * Class Hook
 *
 * @package Tribe\SquareOne\Hooks
 */
abstract class Hook {

	/**
	 * @var OperatingSystem
	 */
	protected $os;

	/**
	 * The root script path
	 *
	 * @var string
	 */
	protected $scriptPath;

	/**
	 * Set the root script path
	 *
	 * Passed via inflection.
	 *
	 * @param  string  $scriptPath
	 */
	public function setScriptPath( string $scriptPath ) {
		$this->scriptPath = $scriptPath;
	}

	/**
	 * Set the Operating System
	 *
	 * Passed via inflection.
	 *
	 * @param  \Tribe\SquareOne\Models\OperatingSystem  $os
	 */
	public function setOperatingSystem( OperatingSystem $os ) {
		$this->os = $os;
	}
}
