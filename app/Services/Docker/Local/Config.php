<?php declare(strict_types=1);

namespace App\Services\Docker\Local;

use App\Contracts\Runner;
use RuntimeException;

/**
 * Local Docker Config
 *
 * @package App\Services\Docker\Local
 */
class Config {

	public const DEFAULT_GID        = 1000;
	public const DEFAULT_UID        = 1000;
	public const ENV_DB_NAME        = 'SQ1_DB_NAME';
	public const ENV_GID            = 'SQ1_GID';
	public const ENV_HOSTIP         = 'HOSTIP';
	public const ENV_HOSTNAME       = 'SQ1_HOSTNAME';
	public const ENV_HOSTNAME_TESTS = 'SQ1_HOSTNAME_TESTS';
	public const ENV_PROJECT_NAME   = 'SQ1_PROJECT_NAME';
	public const ENV_PROJECT_ROOT   = 'SQ1_PROJECT_ROOT';
	public const ENV_UID            = 'SQ1_UID';

	/**
	 * The command runner.
	 */
	protected Runner $runner;

	/**
	 * The path to the project root folder.
	 */
	protected string $projectRoot;

	/**
	 * Override the current directory with a custom path to a project.
	 */
	protected string $path = '';

	/**
	 * Config constructor.
	 *
	 * @param  \App\Contracts\Runner  $runner
	 */
	public function __construct( Runner $runner ) {
		$this->runner = $runner;
	}

	/**
	 * Override the current directory with a custom path to a project.
	 *
	 * @param  string  $path
	 *
	 * @return \App\Services\Docker\Local\Config
	 */
	public function setPath( string $path ): Config {
		$this->path = trim( $path );

		return $this;
	}

	/**
	 * Get the project root.
	 *
	 * @return string
	 */
	public function getProjectRoot(): string {
		if ( empty( $this->projectRoot ) ) {
			while ( true ) {
				// We've reached the root of the operating system, bail out
				if ( '/' === $this->path ) {
					break;
				}

				$path = $this->path ?: getcwd();

				// If these either of these files exist, this is probably a SquareOne project
				$squareOneFiles = [
					"$path/dev/docker/docker-compose.yml",
					"$path/squareone.yml",
				];

				$squareOneFiles = array_filter( $squareOneFiles, 'file_exists' );

				// Check the directory above and continue the loop
				if ( empty( $squareOneFiles ) ) {
					$this->path = dirname( $path );
					continue;
				}

				$this->projectRoot = trim( $path );

				break;
			}
		}

		// We couldn't find a SquareOne project
		if ( empty( $this->projectRoot ) ) {
			throw new RuntimeException( 'Unable to find project root. Are you sure this is a SquareOne Project?' );
		}

		return $this->projectRoot;
	}

	/**
	 * Get the directory of the docker-compose.yml file.
	 *
	 * @return string
	 */
	public function getDockerDir(): string {
		return "{$this->getProjectRoot()}/dev/docker";
	}

	/**
	 * Get the path to the the docker php.ini file.
	 *
	 * @return string
	 */
	public function getPhpIni(): string {
		return "{$this->getDockerDir()}/php/php-ini-overrides.ini";
	}

	/**
	 * Get the project's name.
	 *
	 * @return string
	 */
	public function getProjectName(): string {
		$name = file_get_contents( "{$this->getProjectRoot()}/dev/docker/.projectID" );

		return trim( $name );
	}

	/**
	 * Get the database name.
	 *
	 * @return string
	 */
	public function getDbName(): string {
		$name = $this->getProjectName();

		return str_replace( '-', '_', $name );
	}

	/**
	 * Get the composer volume where the cache and auth.json are stored.
	 *
	 * @return string
	 */
	public function getComposerVolume(): string {
		return "{$this->getProjectRoot()}/dev/docker/composer";
	}

	/**
	 * Get the project's domain
	 *
	 * @param  string  $tld  The top-level domain, e.g. com
	 *
	 * @return string
	 */
	public function getProjectDomain( string $tld = 'tribe' ): string {
		return $this->getProjectName() . '.' . $tld;
	}

	/**
	 * Get the project's test domain
	 *
	 * @param  string  $tld  The top-level domain, e.g. com
	 *
	 * @return string
	 */
	public function getProjectTestDomain( string $tld = 'tribe' ): string {
		return $this->getProjectName() . 'test.' . $tld;
	}

	/**
	 * Get the project's URL
	 *
	 * @param  string  $tld     The top-level domain, e.g. .com
	 * @param  string  $scheme  The scheme, https, http
	 *
	 * @return string
	 */
	public function getProjectUrl( string $tld = 'tribe', string $scheme = 'https' ): string {
		return $scheme . '://' . $this->getProjectDomain( $tld );
	}

	/**
	 * The user's user ID.
	 *
	 * @return int
	 */
	public static function uid(): int {
		return getmyuid() ?: self::DEFAULT_UID;
	}

	/**
	 * The user's group ID.
	 *
	 * @return int
	 */
	public static function gid(): int {
		return getmygid() ?: self::DEFAULT_GID;
	}

}
