<?php declare( strict_types=1 );

namespace Tribe\Sq1\Traits;

use Tribe\Sq1\Commands\ComposerCommands;
use Tribe\Sq1\Commands\GlobalDockerCommands;

/**
 * Use Container Inflection to inject instances because they are set very late in the cycle by Robo.
 *
 * @package Tribe\Sq1\Traits
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
	 * @param  \Tribe\Sq1\Commands\GlobalDockerCommands  $globalTask
	 */
	public function setGlobalDockerTask( GlobalDockerCommands $globalTask ) {
		$this->globalTask = $globalTask;
	}

	/**
	 * Set via Container Inflection.
	 *
	 * @param  \Tribe\Sq1\Commands\ComposerCommands  $composerTask
	 */
	public function setComposerTask( ComposerCommands $composerTask ) {
		$this->composerTask = $composerTask;
	}
}
