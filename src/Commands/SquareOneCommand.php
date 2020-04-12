<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Robo;
use Robo\Tasks;
use Droath\RoboDockerCompose\Task\loadTasks;

/**
 * Sq1 (SquareOne) CLI Command
 *
 * @package Tribe\Sq1
 */
abstract class SquareOneCommand extends Tasks {

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
	 * The docker working directory, e.g. /application/www
	 *
	 * @var string
	 */
	protected $dockerWorkdir;

	/**
	 * The User's Operating System.
	 *
	 * @var string
	 */
	protected $os;

	/**
	 * SquareOneCommand constructor.
	 *
	 */
	public function __construct() {
		$this->dockerWorkdir = Robo::config()->get( 'docker.workdir' );
		$this->os = PHP_OS_FAMILY;
	}

	/**
	 * Robo commands with spaces/arguments come in an array, convert to a string.
	 *
	 * @param  array  $args The Robo command array.
	 *
	 * @return string The command and arguments as a string.
	 */
	protected function prepareCommand( array $args ): string {
		return trim( implode( ' ', $args ) );
	}

}
