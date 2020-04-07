<?php declare( strict_types=1 );

namespace Tribe\Sq1\Traits;

use Robo\Robo;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Local Docker Methods
 *
 * @package Tribe\Sq1\Traits
 */
trait LocalAwareTrait {

	/**
	 * Sets the local docker configuration variables.
	 *
	 * This checks the current folder for build-process.php, and traverses up directories until it finds it.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input|null
	 */
	public function getLocalDockerConfig( ?InputInterface $input ): void {
		if ( ! $this->is_local_command( $input ) ) {
			return;
		}

		$workingDir = getcwd();
		$file       = $workingDir . '/build-process.php';
		$found      = false;

		if ( is_file( $file ) ) {
			$docker_dir     = dirname( $file ) . '/dev/docker';
			$compose_config = [ realpath( $docker_dir . '/docker-compose.yml' ), realpath( $docker_dir . '/docker-compose.override.yml' ) ];
			$project_name   = realpath( $docker_dir . '/.projectID' );
			$found          = true;
		} else {
			$levels = explode( DIRECTORY_SEPARATOR, $workingDir );

			foreach ( $levels as $count => $level ) {
				if ( $count < 1 ) {
					continue;
				}

				if ( is_file( dirname( getcwd(), $count ) . '/build-process.php' ) ) {
					$docker_dir     = dirname( getcwd(), $count ) . '/dev/docker';
					$compose_config = [ realpath( $docker_dir . '/docker-compose.yml' ), realpath( $docker_dir . '/docker-compose.override.yml' ) ];
					$project_name   = realpath( $docker_dir . '/.projectID' );
					$found          = true;
					break;
				}
			}
		}

		if ( ! $found ) {
			$this->yell( 'Unable to find "build-process.php". Are you sure this is a sq1 project?' );
			exit(1);
		}

		Robo::config()->set( 'project_root', dirname( $file ) );
		Robo::config()->set( 'docker_dir', $docker_dir );
		Robo::config()->set( 'compose', array_filter( $compose_config, 'file_exists' ) );
		Robo::config()->set( 'name', trim( file_get_contents( $project_name ) ) );
	}

	/**
	 * Determine if this is a local docker command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input|null
	 *
	 * @return bool
	 */
	protected function is_local_command( ?InputInterface $input ): bool {
		if ( empty( $input ) || empty ( $input->getFirstArgument() ) ) {
			return false;
		}

		$defaultCommands = [
			'list',
			'help',
		];

		if ( in_array( $input->getFirstArgument(), $defaultCommands ) ) {
			return false;
		}

		if ( 'global' === strtok( $input->getFirstArgument(), ':' ) ) {
			return false;
		}

		return true;
	}
}
