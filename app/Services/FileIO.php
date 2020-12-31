<?php declare( strict_types=1 );

namespace App\Services;

use App\Contracts\File;
use Illuminate\Filesystem\Filesystem;

/**
 * A simple file I/O manager.
 *
 * @package App\Services
 */
class FileIO implements File {

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * GitIgnoreFile constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     */
    public function __construct( Filesystem $filesystem ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Whether this file exists.
     *
     * @param  string  $path
     *
     * @return bool
     */
    public function exists( string $path ): bool {
        return (bool) $this->filesystem->exists( $path );
    }

    /**
     * Get the content of a file.
     *
     * @param  string  $path
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get( string $path ): string {
        return $this->filesystem->get( $path );
    }

    /**
     * Whether a file contains a specific string.
     *
     * @param  string  $path
     * @param  string  $content
     *
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function contains( string $path, string $content ): bool {
        $data = $this->filesystem->get( $path );

        return (bool) str_contains( $data, $content );
    }


    /**
     * Add content to a file.
     *
     * @param  string  $path
     * @param  string  $content
     *
     * @return int The number of bytes that were written to the file.
     */
    public function append_content( string $path, string $content ): int {
        return (int) $this->filesystem->append( $path, $content );
    }

    /**
     * Remove content from a file.
     *
     * @param  string  $path
     * @param  string  $content
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function remove_content( string $path, string $content ) {
        $this->replace_content( $path, $content, '' );
    }

    /**
     * Replace content in a file.
     *
     * @param  string  $path
     * @param  string  $search
     * @param  string  $replace
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function replace_content( string $path, string $search, string $replace ) {
        $data = $this->filesystem->get( $path );
        $data = str_replace( $search, $replace, $data );
        $this->filesystem->replace( $path, $data );
    }


}
