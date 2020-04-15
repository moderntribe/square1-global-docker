<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Tribe\SquareOne\Hooks\Docker;

/**
 * Global Docker Commands
 *
 * @package Tribe\SquareOne\Commands
 */
class GlobalDockerCommands extends SquareOneCommand {

	public const PROJECT_NAME = 'global';

	/**
	 * Starts the SquareOne global docker container
	 *
	 * @command global:start
	 */
	public function globalStart() {
		$this->taskDockerComposeUp()
		     ->files( $this->globalComposeFiles() )
		     ->env( 'HOSTIP', getenv( Docker::VAR ) )
		     ->projectName( self::PROJECT_NAME )
		     ->removeOrphans()
		     ->detachedMode()
		     ->run();
	}

	/**
	 * Stops the SquareOne global docker container
	 *
	 * @command global:stop
	 */
	public function globalStop() {
		$this->taskDockerComposeDown()
		     ->files( $this->globalComposeFiles() )
		     ->projectName( self::PROJECT_NAME )
		     ->run();
	}

	/**
	 * Restarts the SquareOne global docker container
	 *
	 * @command global:restart
	 */
	public function globalRestart() {
		$this->taskDockerComposeRestart()
		     ->files( $this->globalComposeFiles() )
		     ->projectName( self::PROJECT_NAME )
		     ->run();
	}

	/**
	 * Stops ALL running docker containers on your system
	 *
	 * @command global:stop-all
	 */
	public function stopAll() {
		$this->say( 'Stopping all Docker containers' );
		$this->_exec( 'docker stop $(docker ps -aq)' );
	}

	/**
	 * Shows all running docker containers
	 *
	 * @command global:status
	 */
	public function globalStatus() {
		$this->_exec( 'docker ps' );
	}

	/**
	 * Displays SquareOne global docker logs
	 *
	 * @command global:logs
	 */
	public function logs() {
		$this->taskDockerComposeLogs()
		     ->files( $this->globalComposeFiles() )
		     ->projectName( self::PROJECT_NAME )
		     ->arg( '-f' )
		     ->run();
	}

	/**
	 * Generates an SSL certificate for a local .tribe domain
	 *
	 * @param  string  $domain  The .tribe domain to generate a certificate for
	 *
	 * @command global:cert
	 *
	 * @TODO    Move actual cert.sh logic into PHP
	 */
	public function globalCert( string $domain ) {
		$this->taskExec( Robo::config()->get( 'docker.cert-sh' ) )
		     ->arg( $domain )
		     ->arg( Robo::config()->get( 'docker.cert-expiry' ) )
		     ->run();
	}

	/**
	 * Start a phpMyAdmin docker container. Default: http://localhost:8080
	 *
	 * @command global:myadmin
	 */
	public function myAdmin() {
		$this->globalStart();

		if ( ! $this->taskDockerStart( 'tribe-phpmyadmin' )->run()->wasSuccessful() ) {

			$this->taskDockerRemove( 'tribe-phpmyadmin' )->run();

			$this->taskDockerRun( 'phpmyadmin/phpmyadmin' )
			     ->option( 'network', Robo::config()->get( 'docker.network' ) )
			     ->option( 'link', Robo::config()->get( 'docker.mysql' ) )
			     ->name( 'tribe-phpmyadmin' )
			     ->publish( Robo::config()->get( 'docker.phpmyadmin-port' ), '80' )
			     ->detached()
			     ->run();

		}

		$this->taskOpenBrowser( sprintf( 'http://localhost:%d', Robo::config()->get( 'docker.phpmyadmin-port' ) ) )->run();
	}

	/**
	 * Get the available global docker compose files
	 *
	 * @return array
	 */
	protected function globalComposeFiles(): array {
		$composeOverride = Robo::config()->get( 'docker.compose-override' );

		return array_filter( [
			Robo::config()->get( 'docker.compose' ),
			file_exists( $composeOverride ) ? $composeOverride : '',
		] );
	}

}
