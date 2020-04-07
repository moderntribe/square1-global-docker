<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;

/**
 * Class ShellTask
 *
 * @package Tribe\Sq1\Tasks
 */
class ShellTask extends LocalDockerTask {

	/**
	 * Gives you a shell into the php-fpm docker container.
	 *
	 * @command shell
	 */
	public function shell() {
		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( 'compose' ) )
		     ->projectName( Robo::config()->get( 'name' ) )
		     ->setContainer( 'php-fpm' )
		     ->exec( '/bin/bash' )
		     ->run();
	}

}
