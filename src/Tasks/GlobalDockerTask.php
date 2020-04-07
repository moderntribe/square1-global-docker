<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;

/**
 * Global Docker Commands
 *
 * @package Tribe\Sq1\Tasks
 */
class GlobalDockerTask extends Sq1Task {

	const PROJECT_NAME = 'global';

	/**
	 * Starts the global docker container.
	 *
	 * @command global:start
	 */
	public function globalStart() {
		$this->taskDockerComposeUp()
		     ->files( $this->global_compose_files() )
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
		     ->files( $this->global_compose_files() )
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
		     ->files( $this->global_compose_files() )
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
	 * Start a phpMyAdmin docker container. Default: http://localhost:8080
	 *
	 * @command global:myadmin
	 */
	public function myAdmin() {
		$this->globalStart();

		if ( ! $this->taskDockerStart( 'tribe-phpmyadmin' )->run() ) {

			$this->taskDockerRun( 'phpmyadmin/phpmyadmin' )
			     ->option( 'network', Robo::config()->get( 'SQ1_DOCKER_NETWORK' ) )
			     ->option( 'link', Robo::config()->get( 'SQ1_DOCKER_MYSQL' ) )
			     ->name( 'tribe-phpmyadmin' )
			     ->publish( Robo::config()->get( 'SQ1_PHPMYADMIN_PORT' ), '80' )
			     ->detached()
			     ->run();

		}

		$this->say( sprintf( 'Started container on http://localhost:%d', Robo::config()->get( 'SQ1_PHPMYADMIN_PORT' ) ) );
	}

	/**
	 * Get the available global docker compose files.
	 *
	 * @return array
	 */
	protected function global_compose_files(): array {
		return array_filter( [
			self::COMPOSE_CONFIG,
			file_exists( self::COMPOSE_OVERRIDE ) ? self::COMPOSE_OVERRIDE : '',
		] );
	}
}
