<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Tribe\SquareOne\Models\LocalDocker;

/**
 * Class ComposerCommands
 *
 * @package Tribe\SquareOne\Commands
 */
class ComposerCommands extends SquareOneCommand {

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
