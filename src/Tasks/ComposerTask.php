<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;

/**
 * Class ComposerTask
 *
 * @package Tribe\Sq1\Tasks
 */
class ComposerTask extends LocalDockerTask {

	/**
	 * Runs a composer command in the local docker container
	 *
	 * @param  string  $composerCommand  The composer command to run
	 *
	 * @command composer
	 */
	public function composer( string $composerCommand ) {
		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( 'compose' ) )
		     ->projectName( Robo::config()->get( 'name' ) )
		     ->setContainer( 'php-fpm' )
		     ->exec( sprintf( 'composer %s -d %s', trim( $composerCommand ), $this->dockerWorkdir ) )
		     ->run();
	}

}
