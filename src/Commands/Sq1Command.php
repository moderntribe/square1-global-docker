<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Robo;
use Tribe\Sq1\Exceptions\Sq1Exception;
use Tribe\Sq1\Traits\LocalAwareTrait;
use Droath\RoboDockerCompose\Task\loadTasks;

/**
 * Sq1 CLI Command
 *
 * @package Tribe\Sq1
 */
abstract class Sq1Command extends \Robo\Tasks {

	use loadTasks;
	use LocalAwareTrait;

	/**
	 * The root path of the script
	 */
	CONST SCRIPT_PATH = __DIR__ . '/../../';

	/**
	 * The path to the docker-compose.yml file
	 */
	CONST COMPOSE_CONFIG = self::SCRIPT_PATH . 'global/docker-compose.yml';

	/**
	 * The docker working directory, e.g. /application/www
	 *
	 * @var string
	 */
	protected $dockerWorkdir;

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

	/**
	 * Runs a composer command in the local docker container
	 *
	 * @param string $composerCommand The composer command to run
	 *
	 * @command composer
	 *
	 * @throws Sq1Exception
	 */
	public function composer( string $composerCommand ) {
		$config = $this->getLocalDockerConfig();

		$this->taskDockerComposeExecute()
		     ->file( $config['compose'] )
		     ->projectName( $config['name'] )
		     ->setContainer( 'php-fpm' )
		     ->exec( sprintf( 'composer %s -d %s', trim( $composerCommand ), $this->dockerWorkdir ) )
		     ->run();
	}

}
