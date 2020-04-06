<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;

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
		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( 'compose' ) )
		     ->projectName( Robo::config()->get( 'name' ) )
		     ->setContainer( 'php-fpm' )
		     ->exec( sprintf( 'composer %s -d %s', trim( implode( ' ', $args ) ), $this->dockerWorkdir ) )
		     ->run();
	}

}
