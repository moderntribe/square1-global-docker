<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Robo\Result;
use Tribe\SquareOne\Models\LocalDocker;

/**
 * WP CLI Commands
 *
 * @package Tribe\SquareOne\Commands
 */
class WpCliCommands extends LocalDockerCommands {

	/**
	 * Run WP CLI commands in the SquareOne local container
	 *
	 * @command wp
	 *
	 * @option  $xdebug Run with Xdebug enabled
	 * @option  $return Return the command's result, which requires no TTY
	 *
	 * @usage   e.g. so wp --xdebug -- option get home --format=json
	 *
	 * @param   array  $args  The WP CLI command and arguments.
	 * @param   array  $opts  The options.
	 *
	 * @return \Robo\Result
	 */
	public function wp( array $args, array $opts = [ 'xdebug|x' => false, 'return' => false ] ): Result {
		$command     = $this->prepareCommand( $args );
		$projectName = Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME );

		/** @var \Robo\Collection\CollectionBuilder $task */
		$task = $this->taskDockerComposeExecute()
		             ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		             ->projectName( $projectName )
		             ->setContainer( 'php-fpm' );

		if ( ! empty( $opts['xdebug'] ) ) {
			$task = $task->envVariable( 'PHP_IDE_CONFIG', "serverName=${projectName}.tribe" )
			             ->exec( sprintf( 'php -dxdebug.remote_autostart=1 -dxdebug.remote_host=host.tribe -dxdebug.remote_enable=1 /usr/local/bin/wp --allow-root %s',
				             $command ) );
		} else {
			$task = $task->envVariable( 'WP_CLI_PHP_ARGS', '' )
			             ->exec( sprintf( 'wp --allow-root %s', $command ) );
		}

		// Disable docker compose TTY / interactive so that Robo/Result::getMessage() is actually populated
		if ( ! empty( $opts['return'] ) ) {
			$task = $task->disablePseudoTty()
			             ->interactive( false );
		}

		return $task->run();
	}

}
