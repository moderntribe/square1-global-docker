<?php declare( strict_types=1 );

namespace App\Services\Config;

use Illuminate\Filesystem\Filesystem;

/**
 * Extend to modify SquareOne default configurations.
 *
 * @package App\Services\Config
 */
abstract class BaseConfig {

    /**
     * Filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The SquareOne configuration directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * Github constructor.
     *
     * @param  Filesystem  $filesystem
     * @param  string      $directory
     */
    public function __construct( Filesystem $filesystem, string $directory ) {
        $this->filesystem = $filesystem;
        $this->directory  = $directory . '/defaults';
    }

}
