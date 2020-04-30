<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

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
 * @package Tribe\SquareOne\Commands
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
		$configDir       = Robo::config()->get( 'squareone.config-dir' );
		$this->cacheFile = sprintf( '%s/%s', $configDir, self::CACHE_FILE_NAME );
	}

	/**
	 * Check if there is an updated phar to self update
	 *
	 * @command self:update-check
	 *
	 * @param  array  $opts  The options.
	 *
	 */
	public function updateCheck( array $opts = [ 'show-existing' => true ] ): void {
		$attempts = 0;
		$release  = $this->getCachedReleaseData();

		while ( $attempts < 2 ) {

			if ( empty( $release->checked ) || (int) $release->checked < strtotime( '-' . self::TIME_BETWEEN_CHECKS ) ) {
				$release = $this->getLatestReleaseFromGitHub();

				// Attempt an authenticated request if this one failed
				if ( empty( $release ) ) {
					$this->yell( 'Unable to fetch update data from the GitHub API' );
					$token   = $this->ask( 'Enter your GitHub token to try an authenticated API request:' );
					$release = $this->getLatestReleaseFromGitHub( $token );
					$attempts ++;
					continue;
				}
			} else {
				break;
			}

		}

		if ( empty( $release ) ) {
			$this->say( 'An error occurred while checking for updates.' );
			return;
		}

		$this->saveReleaseData( $release );

		$shouldUpdate = Comparator::greaterThan( $release->version, $this->version );

		if ( $shouldUpdate ) {
			$this->say( sprintf( '<question>A new version "%s" is available! run "so self:update" to update now.</question>', $release->version ) );
		} else if ( $opts['show-existing'] ) {
			$this->say( sprintf( "You're running the latest version: %s", $this->version ) );
		}
	}

	/**
	 * Get the latest release from the GitHub API
	 *
	 * @param  string  $token The GitHub Token
	 *
	 * @return object|null
	 */
	protected function getLatestReleaseFromGitHub( string $token = '' ): ?object {

		/** @var \EauDeWeb\Robo\Task\Curl\Curl $curl */
		$curl = $this->taskCurl( self::UPDATE_URL )
		             ->silent( true );

		if ( $token ) {
			$header = sprintf( 'Authorization: token %s', trim( $token ) );
			$curl->header( $header );
		}

		$json = $curl->run()->getMessage();

		if ( empty( $json ) ) {
			return null;
		}

		$result = json_decode( $json, true );

		if ( empty( $result['tag_name'] ) ) {
			return null;
		}

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
	 * @return \Tribe\SquareOne\Commands\UpdateCommands
	 */
	public function setVersion( string $version ): self {
		$this->version = $version;

		return $this;
	}

}
