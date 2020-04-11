<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;
use Tribe\Sq1\Models\LocalDocker;

/**
 * Class ShellTask
 *
 * @package Tribe\Sq1\Tasks
 */
class ShellTask extends LocalDockerTask {

	/**
	 * Gives you a shell into the php-fpm docker container
	 *
	 * @command shell
	 */
	public function shell() {
		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
		     ->setContainer( 'php-fpm' )
		     ->exec( '/bin/bash' )
		     ->run();
	}

}
