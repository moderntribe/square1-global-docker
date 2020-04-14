<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Robo;
use Tribe\Sq1\Models\LocalDocker;

/**
 * Run automated test commands
 *
 * @package Tribe\Sq1\Tasks
 */
class TestCommands extends LocalDockerCommands {

	/**
	 * Run Codeception tests
	 *
	 * @command test
	 *
	 * @option  xdebug Run with Xdebug enabled. Default: false.
	 * @option  clean Clean Codeception output directory and generated code. Default: true.
	 * @usage   e.g. sq1 test -- run integration
	 *
	 * @param  array  $args  The Codeception command and arguments.
	 * @param  array  $opts  The options.
	 */
	public function test( array $args, array $opts = [ 'xdebug' => false, 'clean' => true ] ): void {
		$command = $this->prepareCommand( $args );

		if ( $opts['clean'] ) {
			$this->runCodecept( 'clean' );
		}

		if ( $opts['xdebug'] ) {
			$this->runCodeceptX( $command );
		} else {
			$this->runCodecept( $command );
		}
	}

	/**
	 * Run a Codeception command in the Docker container.
	 *
	 * @param  string  $command  The Codeception command.
	 */
	protected function runCodecept( string $command ): void {
		$projectName = Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME );

		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( $projectName )
		     ->setContainer( Robo::config()->get( 'tests.php-container' ) )
		     ->envVariable( 'COMPOSE_INTERACTIVE_NO_CLI', 1 )
		     ->envVariable( 'PHP_IDE_CONFIG', "serverName=${projectName}.tribe" )
		     ->exec( sprintf( 'php -dxdebug.remote_autostart=0 -dxdebug.remote_enable=0 %s -c "%s" %s',
			     $this->dockerWorkdir . '/vendor/bin/codecept',
			     $this->dockerWorkdir . '/dev/tests',
			     $command
		     ) )
		     ->run();
	}

	/**
	 * Run a Codeception command in the Docker container with Xdebug enabled.
	 *
	 * @param  string  $command  The Codeception command.
	 */
	protected function runCodeceptX( string $command ): void {
		$projectName = Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME );

		$this->taskDockerComposeExecute()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( $projectName )
		     ->setContainer( Robo::config()->get( 'tests.php-container' ) )
		     ->envVariable( 'COMPOSE_INTERACTIVE_NO_CLI', 1 )
		     ->envVariable( 'PHP_IDE_CONFIG', "serverName=${projectName}.tribe" )
		     ->exec( sprintf( 'php -dxdebug.remote_autostart=1 -dxdebug.remote_host=host.tribe -dxdebug.remote_enable=1 %s -c "%s" %s %s',
			     $this->dockerWorkdir . '/vendor/bin/codecept',
			     $this->dockerWorkdir . '/dev/tests',
			     $command,
			     implode( ' ', $this->getCodeceptionOverrides() )
		     ) )
		     ->run();
	}

	/**
	 * Get Codeception Overrides when using xdebug.
	 *
	 * @return array
	 */
	protected function getCodeceptionOverrides(): array {
		return Robo::config()->get( 'tests.xdebug-overrides' );
	}

}
