<?php declare( strict_types=1 );

namespace Tribe\Sq1\Traits;

use Tribe\Sq1\Exceptions\Sq1Exception;

/**
 * Local Docker Methods
 *
 *
 * @package Tribe\Sq1\Traits
 */
trait LocalAwareTrait {

	/**
	 * Returns the current local docker config.
	 *
	 * This checks the current folder for build-process.php, and traverses up directories until it finds it.
	 *
	 * @return array
	 *
	 * @throws Sq1Exception
	 */
	protected function getLocalDockerConfig(): array {
		$workingDir = getcwd();
		$file       = $workingDir . '/build-process.php';

		if ( ! is_file( $file  ) ) {
			$levels = explode( DIRECTORY_SEPARATOR, $workingDir );

			foreach ( $levels as $count => $level ) {
				if ( $count < 1 ) {
					continue;
				}

				if ( is_file( dirname( getcwd(), $count ) . '/build-process.php' ) ) {
					$docker_dir     = dirname( getcwd(), $count ) . '/dev/docker';
					$compose_config = [ realpath( $docker_dir . '/docker-compose.yml' ), realpath( $docker_dir . '/docker-compose.override.yml' ) ];
					$project_name   = realpath( $docker_dir . '/.projectID' );

					return $this->getConfig( $docker_dir, $compose_config, $project_name );
				}
			}

		} else {
			$docker_dir     = dirname( $file ) . '/dev/docker';
			$compose_config = [ realpath( $docker_dir . '/docker-compose.yml' ), realpath( $docker_dir . '/docker-compose.override.yml' ) ];
			$project_name   = realpath( $docker_dir . '/.projectID' );

			return $this->getConfig( $docker_dir, $compose_config, $project_name );
		}

		throw new Sq1Exception( 'Unable to find "build-process.php". Are you sure this is a sq1 project?' );
	}

	/**
	 * Returns the current project's sq1 config.
	 *
	 * @param  string    $docker_dir The path to the docker directory
	 * @param  string[]  $compose_config The path to the docker-compose.yml docker-compose.override.yml files
	 * @param  string    $project_name The project's name, as used by docker-compose
	 *
	 * @return array
	 */
	private function getConfig( string $docker_dir, array $compose_config, string $project_name ): array {
		return [
			'name'       => trim( file_get_contents( $project_name ) ),
			'docker_dir' => $docker_dir,
			'compose'    => array_filter( $compose_config, 'file_exists' ),
		];
	}

}
