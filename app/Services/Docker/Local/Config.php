<?php declare( strict_types=1 );

namespace App\Services\Docker\Local;

use RuntimeException;
use App\Contracts\Runner;

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
     * Config constructor.
     *
     * @param  \App\Contracts\Runner  $runner
     */
    public function __construct( Runner $runner ) {
        $this->runner = $runner;
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

                // Check if we're in a submodule first
                $response = $this->runner->with( [
                    'path' => $this->path,
                ] )->run( 'git -C {{ $path }} rev-parse --show-superproject-working-tree' );

                if ( empty( trim( (string) $response ) ) ) {
                    $response = $this->runner->with( [
                        'path' => $this->path,
                    ] )->run( 'git -C {{ $path }} rev-parse --show-toplevel' );
                }

                if ( ! $response->ok() ) {
                    throw new RuntimeException( 'Unable to find project root. Are you sure this is a SquareOne Project?' );
                }

                $response = trim( (string) $response );

                // If these files exist, this is probably a SquareOne project.
                $squareOneFiles = [
                    "{$response}/dev/docker/docker-compose.yml",
                    "{$response}/squareone.yml",
                ];

                $squareOneFiles = array_filter( $squareOneFiles, 'file_exists' );

                if ( empty( $squareOneFiles ) ) {
                    $this->path = dirname( $response );

                    // Throw an error if we reach the top of the filesystem.
                    if ( '/' === $this->path ) {
                        throw new RuntimeException( sprintf( 'Unable to find /dev/docker/docker-compose.yml or ./squareone.yml in %s. Are you sure this is a SquareOne Project?',
                            $response ) );
                    }

                    continue;
                }

                $this->projectRoot = trim( $response );

                break;
            }

        }

        return $this->projectRoot;
    }

    /**
     * Get the docker-compose.yml file to pass to docker-compose.
     *
     * @return string
     */
    public function getComposeFile(): string {
        $files = [
            "{$this->getProjectRoot()}/dev/docker/docker-compose.override.yml",
            "{$this->getProjectRoot()}/dev/docker/docker-compose.yml",
        ];

        return current( array_filter( $files, 'file_exists' ) );
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

}
