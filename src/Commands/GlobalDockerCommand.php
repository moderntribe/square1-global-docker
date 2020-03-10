<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

class GlobalDockerCommand extends Sq1Command {

	const PROJECT_NAME = 'global';

	/**
	 * Starts the global docker container.
	 *
	 * @command global:start
	 */
	public function globalStart() {
		$this->taskDockerComposeUp()
		     ->file( self::COMPOSE_CONFIG )
		     ->projectName( self::PROJECT_NAME )
		     ->detachedMode()
		     ->run();
	}

	/**
	 * Stops the global docker container
	 *
	 * @command global:stop
	 */
	public function globalStop() {
		$this->taskDockerComposeDown()
		     ->file( self::COMPOSE_CONFIG )
		     ->projectName( self::PROJECT_NAME )
		     ->run();
	}

	/**
	 * Restarts the global docker container
	 *
	 * @command global:restart
	 */
	public function globalRestart() {
		$this->taskDockerComposeRestart()
		     ->file( self::COMPOSE_CONFIG )
		     ->projectName( self::PROJECT_NAME )
		     ->run();
	}

	/**
	 * Stops ALL docker containers on your system
	 *
	 * @command global:stop-all
	 */
	public function stopAll() {
		$this->say( 'Stopping all Docker containers' );
		$this->_exec( 'docker stop $(docker ps -aq)' );
	}

	/**
	 * Check if the global containers are running
	 *
	 * @command global:status
	 */
	public function globalStatus() {
		$this->_exec( 'docker ps' );
	}

	/**
	 * Generates an SSL certificate for a local .tribe domain
	 *
	 * @param string $domain The .tribe domain to generate a certificate for
	 *
	 * @command global:cert
	 *
	 * @TODO Move actual cert.sh logic into PHP
	 */
	public function globalCert( string $domain ) {
		$this->taskExec( self::SCRIPT_PATH . 'global/cert.sh' )
		     ->arg( $domain )
		     ->run();
	}

	/**
	 * Start a phpMyAdmin docker container on port 8080
	 *
	 * @command global:myadmin
	 */
	public function myAdmin() {
		$this->globalStart();

		if ( ! $this->taskDockerStart( 'tribe-phpmyadmin' )->run() ) {

			$this->taskDockerRun( 'phpmyadmin/phpmyadmin' )
			     ->option( 'network', 'global_proxy' )
			     ->option( 'link', 'tribe-mysql:db' )
			     ->name( 'tribe-phpmyadmin' )
			     ->publish( '8080', '80' )
			     ->detached()
			     ->run();

		}
	}
}
