<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Traits;

use Robo\Robo;
use Symfony\Component\Console\Input\InputInterface;
use Tribe\SquareOne\Models\LocalDocker;

/**
 * Local Docker Methods
 *
 * @package Tribe\SquareOne\Traits
 */
trait LocalAwareTrait {

	/**
	 * Sets the local docker configuration variables. Uses consolidation/annotated-command hooks to run when each command does
	 *
	 * @hook pre-init *
	 *
	 * This checks the current folder for specific SquareOne files, and traverses up directories until it finds a match
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

		// Get a list of files from the config that are unique to SquareOne projects
		$files = Robo::config()->get( 'local-docker.files' );

		// Track if we found any identifying files
		$found = false;

		// Loop over the files until we get a hit
		foreach ( $files as $file ) {
			$rootFile = sprintf( '%s/%s', $workingDir, $file );

			if ( is_file( $rootFile ) ) {
				$projectRoot   = dirname( $rootFile );
				$dockerDir     = $projectRoot . '/dev/docker';
				$composeConfig = [
					realpath( $dockerDir . '/docker-compose.yml' ),
					realpath( $dockerDir . '/docker-compose.override.yml' ),
					realpath( $dockerDir . '/' . Robo::config()->get( 'local-docker.compose-override' ) ),
				];
				$projectName   = realpath( $dockerDir . '/.projectID' );
				$found         = true;
			} else {
				$levels = explode( DIRECTORY_SEPARATOR, $workingDir );

				foreach ( $levels as $count => $level ) {
					if ( $count < 1 ) {
						continue;
					}

					if ( is_file( dirname( getcwd(), $count ) . '/' . $file ) ) {
						$projectRoot   = dirname( getcwd(), $count );
						$dockerDir     = $projectRoot . '/dev/docker';
						$composeConfig = [
							realpath( $dockerDir . '/docker-compose.yml' ),
							realpath( $dockerDir . '/docker-compose.override.yml' ),
							realpath( $dockerDir . '/' . Robo::config()->get( 'local-docker.compose-override' ) ),
						];
						$projectName   = realpath( $dockerDir . '/.projectID' );
						$found         = true;
						break;
					}
				}
			}

		}

		if ( ! $found ) {
			$this->yell( 'Unable to launch project. Are you sure this is a SquareOne project?' );
			exit( E_ERROR );
		}

		$this->maybeLoadConfig( $projectRoot );

		Robo::config()->set( LocalDocker::CONFIG_PROJECT_ROOT, $projectRoot );
		Robo::config()->set( LocalDocker::CONFIG_PROJECT_NAME, trim( file_get_contents( $projectName ) ) );
		Robo::config()->set( LocalDocker::CONFIG_DOCKER_DIR, $dockerDir );
		Robo::config()->set( LocalDocker::CONFIG_DOCKER_COMPOSE, array_filter( array_unique( $composeConfig ), 'file_exists' ) );
		Robo::config()->set( LocalDocker::CONFIG_COMPOSER_CACHE, $dockerDir . '/' . Robo::config()->get( 'local-docker.composer-cache' ) );
		Robo::config()->set( LocalDocker::CONFIG_COMPOSER_AUTH, $dockerDir . '/' . Robo::config()->get( 'local-docker.composer-auth' ) );
	}

	/**
	 * Load extra configuration options if the project has a squareone.yml.
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
