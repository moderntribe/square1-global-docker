<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Traits;

use Tribe\SquareOne\Commands\ComposerCommands;
use Tribe\SquareOne\Commands\GlobalDockerCommands;

/**
 * Use Container Inflection to inject instances because they are set very late in the cycle by Robo.
 *
 * @package Tribe\SquareOne\Traits
 */
trait InflectionAwareTrait {

	/**
	 * @var GlobalDockerCommands
	 */
	protected $globalTask = null;

	/**
	 * @var ComposerCommands
	 */
	protected $composerTask = null;

	/**
	 * Set via Container Inflection.
	 *
	 * @param  \Tribe\SquareOne\Commands\GlobalDockerCommands  $globalTask
	 */
	public function setGlobalDockerTask( GlobalDockerCommands $globalTask ) {
		$this->globalTask = $globalTask;
	}

	/**
	 * Set via Container Inflection.
	 *
	 * @param  \Tribe\SquareOne\Commands\ComposerCommands  $composerTask
	 */
	public function setComposerTask( ComposerCommands $composerTask ) {
		$this->composerTask = $composerTask;
	}
}
