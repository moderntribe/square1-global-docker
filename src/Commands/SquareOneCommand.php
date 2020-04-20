<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Robo\Tasks;
use Droath\RoboDockerCompose\Task\loadTasks;

/**
 * SquareOne (so) CLI Command
 *
 * @package Tribe\SquareOne
 */
abstract class SquareOneCommand extends Tasks {

	use loadTasks;

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
	 * The path to this script
	 *
	 * @var string
	 */
	protected $scriptPath;

	/**
	 * SquareOneCommand constructor.
	 *
	 */
	public function __construct() {
		$this->dockerWorkdir = Robo::config()->get( 'docker.workdir' );
		$this->os            = PHP_OS_FAMILY;
	}

	/**
	 * Set via inflection.
	 *
	 * @param  string  $scriptPath The script's path
	 *
	 * @return $this
	 */
	public function setScriptPath( string $scriptPath ): self {
		$this->scriptPath = $scriptPath;

		return $this;
	}

	/**
	 * Robo commands with spaces/arguments come in an array, convert to a string.
	 *
	 * @param  array  $args  The Robo command array.
	 *
	 * @return string The command and arguments as a string.
	 */
	protected function prepareCommand( array $args ): string {
		return trim( implode( ' ', array_map( 'escapeshellarg', $args ) ) );
	}

}
