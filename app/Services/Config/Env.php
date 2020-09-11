<?php declare( strict_types=1 );

namespace App\Services\Config;

use M1\Env\Parser;
use Illuminate\Filesystem\Filesystem;

/**
 * Manages composer .env files for SquareOne local projects.
 *
 * @package App\Services\Config
 */
class Env extends BaseConfig {

    /**
     * Name & relative path of default composer .env file
     */
    public const ENV_FILE = '/.env';

    /**
     * Path to the default .env file.
     *
     * @var string
     */
    protected $envFile;

    /**
     * Env constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  string                             $directory
     */
    public function __construct( Filesystem $filesystem, string $directory ) {
        parent::__construct( $filesystem, $directory );
        $this->envFile = $this->directory . self::ENV_FILE;
    }

    /**
     * Get the environment variables from the default .env file
     *
     * @return array
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getVars(): array {
        return Parser::parse( $this->filesystem->get( $this->envFile ) );
    }

    /**
     * If the default .env file exists.
     *
     * @return bool
     */
    public function exists(): bool {
        return (bool) $this->filesystem->exists( $this->envFile );
    }

    /**
     * Save the default .env file
     *
     * @param  string  $content  The content to write to the .env file
     *
     * @return bool
     */
    public function save( string $content ): bool {
        return (bool) $this->filesystem->put( $this->envFile, $content );
    }

    /**
     * Copy the default .env to the local project's root directory.
     *
     * @param  string  $projectRoot  The root directory for the local project.
     *
     * @return bool
     */
    public function copy( string $projectRoot ): bool {
        return (bool) $this->filesystem->copy( $this->envFile, $projectRoot . self::ENV_FILE );
    }

    /**
     * Compare the keys of two .env files and return the difference
     *
     * @param  string  $sampleEnvFile  The SquareOne local .env.sample path
     *
     * @return array The missing environment variables
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function diff( string $sampleEnvFile ): array {
        $local  = Parser::parse( $this->filesystem->get( $sampleEnvFile ) );
        $global = $this->getVars();

        return array_diff_key( $local, $global );
    }

}
