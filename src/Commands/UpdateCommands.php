<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Tasks;
use EauDeWeb\Robo\Task\Curl\loadTasks;
use Composer\Semver\Comparator;

/**
 * Class UpdateCommands
 *
 * @note the self:update command is automatically provided by Robo
 *
 * @package Tribe\Sq1\Commands
 */
class UpdateCommands extends Tasks {

	use loadTasks;

	public const TIME_BETWEEN_CHECKS = '7 days';
	public const UPDATE_URL          = 'https://api.github.com/repos/moderntribe/square1-global-docker/releases/latest';

	/**
	 * The current phar version
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Check if there is an updated phar to self update
	 *
	 * @command self:update-check
	 */
	public function updateCheck() {
		$release = $this->getLatestRelease();

		$shouldUpdate = Comparator::greaterThan( $release->version, $this->version );

		if ( $shouldUpdate ) {
			$this->say( sprintf( '<question>A new version "%s" is available! run sq1 self:update to update now.</question>', $release->version ) );
		} else {
			$this->say( sprintf( "You're running the latest version: %s", $this->version ) );
		}
	}

	/**
	 * Get the latest release from the GitHub API
	 *
	 * @return object
	 */
	protected function getLatestRelease() {
		$json = $this->taskCurl( self::UPDATE_URL )->silent( true )->run()->getMessage();

		$result = json_decode( $json, true );

		return (object) [
			'version' => $result['tag_name'],
			'checked' => time(),
		];
	}

	/**
	 * Set the current version, set via inflection
	 *
	 * @param  string  $version  The current phar version
	 */
	public function setVersion( string $version ) {
		$this->version = $version;
	}

}
