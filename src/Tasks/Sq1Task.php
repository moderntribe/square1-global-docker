<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;
use Robo\Tasks;
use Droath\RoboDockerCompose\Task\loadTasks;

/**
 * Sq1 CLI Command
 *
 * @package Tribe\Sq1
 */
abstract class Sq1Task extends Tasks {

	use loadTasks;

	/**
	 * The root path of the script
	 */
	const SCRIPT_PATH = __DIR__ . '/../../';

	/**
	 * The path to the docker-compose.yml file
	 */
	const COMPOSE_CONFIG = self::SCRIPT_PATH . 'global/docker-compose.yml';

	/**
	 * The path to the docker-compose.override.yml file
	 */
	const COMPOSE_OVERRIDE = self::SCRIPT_PATH . 'global/docker-compose.override.yml';

	/**
	 * The docker working directory, e.g. /application/www
	 *
	 * @var string
	 */
	protected $dockerWorkdir;

	/**
	 * Sq1Task constructor.
	 *
	 */
	public function __construct() {
		$this->dockerWorkdir = Robo::config()->get( 'SQ1_DOCKER_WORKDIR' );
	}

	/**
	 * Clones square-one src and creates a new sq1 project
	 *
	 * @command create
	 *
	 * @usage <project_name>
	 */
	public function create( string $project ) {
		$name = $this->ask( 'What is the name of your new project? e.g. ' );
		$this->say( $name );
	}

}
