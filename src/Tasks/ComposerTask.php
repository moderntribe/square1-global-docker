<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;
use Tribe\Sq1\Models\LocalDocker;

/**
 * Class ComposerTask
 *
 * @package Tribe\Sq1\Tasks
 */
class ComposerTask extends Sq1Task {

	/**
	 * Runs a composer command in the local docker container
	 *
	 * @param  array  $args  The composer command to run
	 *
	 * @command composer
	 */
	public function composer( array $args ) {
		$command = $this->prepareCommand( $args );

		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
		     ->setContainer( 'php-fpm' )
		     ->exec( sprintf( 'composer %s -d %s', $command, $this->dockerWorkdir ) )
		     ->run();
	}

}
