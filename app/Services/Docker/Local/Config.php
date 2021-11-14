<?php declare( strict_types=1 );

namespace App\Services\Docker\Local;

use App\Contracts\Runner;
use RuntimeException;

/**
 * Local Docker Config
 *
 * @package App\Services\Docker\Local
 */
class Config {

    public const ENV_UID     = 'SQ1_UID';
    public const ENV_GID     = 'SQ1_GID';
    public const DEFAULT_UID = 1000;
    public const DEFAULT_GID = 1000;

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * The path to the project root folder.
     *
     * @var string
     */
    protected $projectRoot;

    /**
     * Override the current directory with a custom path to a project.
     *
     * @var string
     */
    protected $path = '';

    /**
     * The working directory in the docker container where the application
     * files live.
     *
     * @var string
     */
    protected $workdir = '';

    /**
     * Config constructor.
     *
     * @param  \App\Contracts\Runner  $runner
     * @param  string                 $workdir
     */
    public function __construct( Runner $runner, string $workdir ) {
        $this->runner  = $runner;
        $this->workdir = $workdir;
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

    /**
     * Override the current directory with a custom path to a project.
     *
     * @param  string  $path
     *
     * @return $this
     */
    public function setPath( string $path ) {
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

                $path = $this->path ? $this->path : getcwd();

                // If these either of these files exist, this is probably a SquareOne project
                $squareOneFiles = [
                    "{$path}/dev/docker/docker-compose.yml",
                    "{$path}/squareone.yml",
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
     * Get the project's URL
     *
     * @param  string  $tld     The top-level domain, e.g. .com
     * @param  string  $scheme  The scheme, https, http
     *
     * @return string
     *
     */
    public function getProjectUrl( string $tld = 'tribe', string $scheme = 'https' ): string {
        return $scheme . '://' . $this->getProjectDomain( $tld );
    }

    /**
     * The server path to the application inside the docker container.
     *
     * @return string
     */
    public function getWorkdir(): string {
        return $this->workdir;
    }

}
