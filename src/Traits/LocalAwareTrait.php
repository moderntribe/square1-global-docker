<?php declare( strict_types=1 );

namespace Tribe\Sq1\Traits;

use Robo\Robo;
use Symfony\Component\Console\Input\InputInterface;
use Tribe\Sq1\Models\LocalDocker;

/**
 * Local Docker Methods
 *
 * @package Tribe\Sq1\Traits
 */
trait LocalAwareTrait {

	/**
	 * Sets the local docker configuration variables. Uses consolidation/annotated-command hooks to run when each command does.
	 *
	 * @hook pre-init *
	 *
	 * This checks the current folder for build-process.php, and traverses up directories until it finds it.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input  |null
	 */
	public function getLocalDockerConfig( ?InputInterface $input ): void {
		if ( ! $this->is_local_command( $input ) ) {
			return;
		}

		$optionProjectPath = Robo::config()->get( 'options.project-path.name' );

		// Set a user's custom project path if provided via --project-path=/path/to/project
		$workingDir = ( $input->hasOption( $optionProjectPath ) && is_string( $input->getOption( $optionProjectPath ) ) )
			? $input->getOption( $optionProjectPath ) : getcwd();

		$file  = $workingDir . '/build-process.php';
		$found = false;

		if ( is_file( $file ) ) {
			$projectRoot   = dirname( $file );
			$dockerDir     = $projectRoot . '/dev/docker';
			$composeConfig = [ realpath( $dockerDir . '/docker-compose.yml' ), realpath( $dockerDir . '/docker-compose.override.yml' ) ];
			$projectName   = realpath( $dockerDir . '/.projectID' );
			$found         = true;
		} else {
			$levels = explode( DIRECTORY_SEPARATOR, $workingDir );

			foreach ( $levels as $count => $level ) {
				if ( $count < 1 ) {
					continue;
				}

				if ( is_file( dirname( getcwd(), $count ) . '/build-process.php' ) ) {
					$projectRoot   = dirname( getcwd(), $count );
					$dockerDir     = $projectRoot . '/dev/docker';
					$composeConfig = [ realpath( $dockerDir . '/docker-compose.yml' ), realpath( $dockerDir . '/docker-compose.override.yml' ) ];
					$projectName   = realpath( $dockerDir . '/.projectID' );
					$found         = true;
					break;
				}
			}
		}

		if ( ! $found ) {
			$this->yell( 'Unable to find "build-process.php". Are you sure this is a sq1 project?' );
			exit( E_ERROR );
		}

		$this->maybeLoadConfig( $projectRoot );

		Robo::config()->set( LocalDocker::CONFIG_PROJECT_ROOT, $projectRoot );
		Robo::config()->set( LocalDocker::CONFIG_PROJECT_NAME, trim( file_get_contents( $projectName ) ) );
		Robo::config()->set( LocalDocker::CONFIG_DOCKER_DIR, $dockerDir );
		Robo::config()->set( LocalDocker::CONFIG_DOCKER_COMPOSE, array_filter( $composeConfig, 'file_exists' ) );
	}

	/**
	 * Load extra configuration options if the project has a sq1.yml.
	 *
	 * @param  string  $projectRoot
	 */
	protected function maybeLoadConfig( string $projectRoot ): void {
		$localConfig = $projectRoot . '/' . LocalDocker::CONFIG_FILE;

		if ( file_exists( $localConfig ) ) {
			Robo::loadConfiguration( [ $localConfig ], Robo::config() );
		}
	}

	/**
	 * Determine if this is a local docker command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input  |null
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
