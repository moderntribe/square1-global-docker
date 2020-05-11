<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Tribe\SquareOne\Models\LocalDocker;

/**
 * Class ShellCommands
 *
 * @package Tribe\SquareOne\Commands
 */
class ShellCommands extends LocalDockerCommands {

	/**
	 * Gives you a shell into the php-fpm docker container
	 *
	 * @command shell
	 */
	public function shell() {
		$projectName = Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME );

		$this->say( sprintf( 'Launching shell for %s...', $projectName ) );

		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( $projectName )
		     ->setContainer( 'php-fpm' )
		     ->exec( '/bin/bash' )
		     ->run();
	}

}
