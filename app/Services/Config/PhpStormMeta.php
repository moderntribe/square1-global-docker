<?php declare( strict_types=1 );

namespace App\Services\Config;

use Illuminate\Filesystem\Filesystem;

/**
 * Manages the .phpstorm.meta.php file to add better
 * code completion for the PHP-DI container.
 */
class PhpStormMeta extends BaseConfig {

    public const PHAR_META_FILE = '/phpstorm.meta.php';
    public const META_FILE      = '/.phpstorm.meta.php';

    /**
     * Path to the default meta file inside the phar.
     *
     * @var string
     */
    protected $metaFile;

    public function __construct( Filesystem $filesystem, string $directory ) {
        parent::__construct( $filesystem, $directory );
        $this->metaFile = $this->directory . self::PHAR_META_FILE;
    }

    /**
     * Check if this project has a .phpstorm.meta.php file.
     *
     * @param  string  $projectRoot
     *
     * @return bool
     */
    public function existsInProject( string $projectRoot ): bool {
        return $this->filesystem->exists( $projectRoot . self::META_FILE );
    }

    /**
     * Copy the .phpstorm.meta.php file to the project root.
     *
     * @param  string  $projectRoot  The root path of the local project.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return bool
     */
    public function copy( string $projectRoot ): bool {
        $content = $this->filesystem->get( $this->metaFile );

        return (bool) $this->filesystem->put( $projectRoot . self::META_FILE, $content );
    }

}
