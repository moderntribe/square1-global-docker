<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Robo;
use EauDeWeb\Robo\Task\Curl\loadTasks;

/**
 * Copies config files
 *
 * @package Tribe\Sq1\Commands
 */
class ConfigCommands extends SquareOneCommand {

	use loadTasks;

	/**
	 * The path to the configuration file
	 *
	 * @var string
	 */
	protected $configFolder;

	/**
	 * ConfigCommands constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->configFolder = Robo::config()->get( 'vars.config' );
	}

	/**
	 * Copies the sq1.yml file to the local config folder for customization
	 *
	 * @command config:copy
	 */
	public function configCopy() {
		$file = 'sq1.yaml';

		$this->taskCurl( 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/config/sq1.yml' )
		     ->output( sprintf( '%s/%s', $this->configFolder, $file ) )
		     ->run();

		$this->say( sprintf( 'Saved %s to %s', $file, $this->configFolder ) );
	}

	/**
	 * Copies the Global docker-compose.yml file to the local config folder for customization
	 *
	 * @command config:compose-copy
	 */
	public function composeCopy() {
		$file = Robo::config()->get( 'docker.compose-override' );

		$this->taskCurl( 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/global/docker-compose.yml' )
		     ->output( Robo::config()->get( 'docker.compose-override' ) )
		     ->run();

		$this->say( sprintf( 'Saved to %s', $file ) );
	}

}
