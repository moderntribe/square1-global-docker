<?php declare( strict_types=1 );

namespace Tribe\Sq1\Traits;

use Tribe\Sq1\Tasks\ComposerTask;
use Tribe\Sq1\Tasks\GlobalDockerTask;

/**
 * Use Container Inflection to inject instances because they are set very late in the cycle by Robo.
 *
 * @package Tribe\Sq1\Traits
 */
trait InflectionAwareTrait {

	/**
	 * @var GlobalDockerTask
	 */
	protected $globalTask = null;

	/**
	 * @var ComposerTask
	 */
	protected $composerTask = null;

	/**
	 * Set via Container Inflection.
	 *
	 * @param  \Tribe\Sq1\Tasks\GlobalDockerTask  $globalTask
	 */
	public function setGlobalDockerTask( GlobalDockerTask $globalTask ) {
		$this->globalTask = $globalTask;
	}

	/**
	 * Set via Container Inflection.
	 *
	 * @param  \Tribe\Sq1\Tasks\ComposerTask  $composerTask
	 */
	public function setComposerTask( ComposerTask $composerTask ) {
		$this->composerTask = $composerTask;
	}
}
