<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Robo;
use Tribe\Sq1\Models\OS;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Global Docker Commands
 *
 * @package Tribe\Sq1\Tasks
 */
class GlobalDockerCommands extends SquareOneCommand {

	public const PROJECT_NAME = 'global';

	/**
	 * Starts the SquareOne global docker container
	 *
	 * @command global:start
	 */
	public function globalStart() {
		$this->init()
		     ->taskDockerComposeUp()
		     ->files( $this->globalComposeFiles() )
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
		$this->init()
		     ->taskDockerComposeRestart()
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
		$this->taskExec( self::SCRIPT_PATH . 'global/cert.sh' )
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

		$this->say( sprintf( 'Started phpMyAdmin on http://localhost:%d', Robo::config()->get( 'docker.phpmyadmin-port' ) ) );
	}

	/**
	 * Get the available global docker compose files
	 *
	 * @return array
	 */
	protected function globalComposeFiles(): array {
		return array_filter( [
			self::COMPOSE_CONFIG,
			file_exists( self::COMPOSE_OVERRIDE ) ? self::COMPOSE_OVERRIDE : '',
		] );
	}

	/**
	 * Initialize Global Docker
	 *
	 * @return $this
	 */
	protected function init(): self {
		$env = SquareOneCommand::SCRIPT_PATH . 'global/.env';

		$ip = '0.0.0.0';

		// Mac OS defaults.
		$resolverDir  = '/etc/resolver/';
		$resolverFile = 'tribe';

		// Get the Docker host IP from the alpine container.
		if ( OS::MAC_OS === $this->os || OS::WINDOWS === $this->os ) {
			$result = $this->taskDockerRun( 'alpine:3.11.5' )
			               ->args( [ '--rm' ] )
			               ->exec( 'nslookup host.docker.internal. | grep "Address:" | awk \'{ print $2 }\' | tail -1' )
			               ->interactive( false )
			               ->run();

			$ip = $result->getMessage();
		}

		// Get the Docker host IP from the ip command.
		if ( $this->os === OS::LINUX ) {
			$result = $this->taskExec( "ip -4 addr show docker0 | grep -Po 'inet \K[\d.]+'" )
			               ->printOutput( false )
			               ->silent( true )
			               ->run();

			$ip = $result->getMessage();

			$resolverDir  = '/etc/';
			$resolverFile = 'resolv.conf.head';

			// Ubuntu
			if ( is_dir( '/etc/resolvconf/resolv.conf.d/' ) ) {
				$resolverDir  = '/etc/resolvconf/resolv.conf.d/';
				$resolverFile = 'head';
			}

		}

		// Add nameservers
		if ( ! file_exists( $resolverDir . $resolverFile ) ) {
			$this->writeResolver( $resolverDir, $resolverFile );
		}

		// Write docker host IP address to the .env file.
		$this->taskWriteToFile( $env )
		     ->setVerbosityThreshold( VerbosityThresholdInterface::VERBOSITY_DEBUG )
		     ->line( 'HOSTIP={IP}' )
		     ->place( 'IP', $ip )
		     ->run();

		// synchronize VM time with system time
		$this->taskDockerRun( 'phpdockerio/php7-fpm' )
		     ->privileged()
		     ->args( [ '--rm' ] )
		     ->exec( 'date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )
		     ->printOutput( false )
		     ->silent( true )
		     ->run();

		return $this;
	}

	/**
	 * Writes nameservers to a resolver file and copies it to the correct location
	 *
	 * @param  string  $dir           The resolver directory.
	 * @param  string  $fileName      The resolver file name.
	 * @param  string  $nameserverIp  The nameserver IP to add to the file.
	 */
	protected function writeResolver( string $dir, string $fileName, string $nameserverIp = '127.0.0.1' ): void {
		$file    = $dir . $fileName;
		$tmpFile = $this->taskTmpFile()->getPath();

		$this->taskWriteToFile( $tmpFile )
		     ->setVerbosityThreshold( VerbosityThresholdInterface::VERBOSITY_DEBUG )
		     ->line( 'nameserver {IP}' )
		     ->place( 'IP', $nameserverIp )
		     ->run();

		if ( ! is_dir( $dir ) ) {
			$this->taskExec( sprintf( 'sudo mkdir -p %s', $dir ) )->run();
		}

		$this->taskExec( sprintf( 'sudo cp %s %s', $tmpFile, $file ) )->run();

		unset( $tmpFile );
	}

}
