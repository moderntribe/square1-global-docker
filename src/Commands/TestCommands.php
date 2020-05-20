<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Tribe\SquareOne\Models\LocalDocker;

/**
 * Run automated test commands
 *
 * @package Tribe\SquareOne\Commands
 */
class TestCommands extends LocalDockerCommands {

	/**
	 * Run Codeception tests
	 *
	 * @command test
	 *
	 * @option  $xdebug Run with Xdebug enabled
	 * @option  $no-clean Do not run the codecept clean command first
	 *
	 * @usage   e.g. so test --xdebug -- run integration
	 *
	 * @param   array  $args  The Codeception command and arguments.
	 * @param   array  $opts  The options.
	 */
	public function test( array $args, array $opts = [ 'xdebug|x' => false, 'no-clean' => false ] ): void {
		$command = $this->prepareCommand( $args );

		if ( empty( $opts['no-clean'] ) ) {
			$this->runCodecept( 'clean' );
		}

		if ( ! empty( $opts['xdebug'] ) ) {
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
