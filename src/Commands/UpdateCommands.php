<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Robo;
use Robo\Tasks;
use EauDeWeb\Robo\Task\Curl\loadTasks;
use Robo\Contract\VerbosityThresholdInterface;
use Composer\Semver\Comparator;
use stdClass;

/**
 * Class UpdateCommands
 *
 * @note    the self:update command is automatically provided by Robo
 *
 * @package Tribe\Sq1\Commands
 */
class UpdateCommands extends Tasks {

	use loadTasks;

	public const CACHE_FILE_NAME     = '.release';
	public const TIME_BETWEEN_CHECKS = '7 days';
	public const UPDATE_URL          = 'https://api.github.com/repos/moderntribe/square1-global-docker/releases/latest';

	/**
	 * The current phar version
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * The full path to the cache file
	 *
	 * @var string
	 */
	protected $cacheFile;

	/**
	 * UpdateCommands constructor.
	 */
	public function __construct() {
		$configDir       = Robo::config()->get( 'vars.config' );
		$this->cacheFile = sprintf( '%s/%s', $configDir, self::CACHE_FILE_NAME );
	}

	/**
	 * Check if there is an updated phar to self update
	 *
	 * @command self:update-check
	 *
	 * @param  array  $opts The options.
	 */
	public function updateCheck( array $opts = [ 'show-existing' => true ] ) {
		$release = $this->getCachedReleaseData();

		if ( empty( $release->checked ) || (int) $release->checked < strtotime( '-' . self::TIME_BETWEEN_CHECKS ) ) {
			$release = $this->getLatestReleaseFromGitHub();
			$this->saveReleaseData( $release );
		}

		$shouldUpdate = Comparator::greaterThan( $release->version, $this->version );

		if ( $shouldUpdate ) {
			$this->say( sprintf( '<question>A new version "%s" is available! run sq1 self:update to update now.</question>', $release->version ) );
		} else if ( $opts['show-existing'] ) {
			$this->say( sprintf( "You're running the latest version: %s", $this->version ) );
		}
	}

	/**
	 * Get the latest release from the GitHub API
	 *
	 * @return object
	 */
	protected function getLatestReleaseFromGitHub(): object {
		$json = $this->taskCurl( self::UPDATE_URL )
		             ->silent( true )
		             ->run()
		             ->getMessage();

		$result = json_decode( $json, true );

		return (object) [
			'version' => $result['tag_name'],
			'checked' => time(),
		];
	}

	/**
	 * Get the release data from the cache file
	 *
	 * @return object The release object
	 */
	protected function getCachedReleaseData(): object {
		if ( ! file_exists( $this->cacheFile ) ) {
			return new stdClass();
		}

		$content = file_get_contents( $this->cacheFile );

		if ( empty( $content ) ) {
			return new stdClass();
		}

		return (object) json_decode( $content );
	}

	/**
	 * Write release data to the cache file
	 *
	 * @param  object  $release
	 */
	protected function saveReleaseData( object $release ): void {
		$this->taskWriteToFile( $this->cacheFile )
		     ->line( json_encode( $release ) )
		     ->setVerbosityThreshold( VerbosityThresholdInterface::VERBOSITY_DEBUG )
		     ->run();
	}

	/**
	 * Set the current version, set via inflection
	 *
	 * @param  string  $version  The current phar version
	 *
	 * @return \Tribe\Sq1\Commands\UpdateCommands
	 */
	public function setVersion( string $version ): self {
		$this->version = $version;

		return $this;
	}

}
