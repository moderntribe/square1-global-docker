<?php declare( strict_types=1 );

namespace App\Contracts;

/**
 * Interface File
 *
 * @package App\Contracts
 */
interface File {

    /**
     * Whether this file exists.
     *
     * @param  string  $path
     *
     * @return bool
     */
    public function exists( string $path ): bool;

    /**
     * Get the content of a file.
     *
     * @param  string  $path
     *
     * @return string
     */
    public function get( string $path ): string;

    /**
     * Whether a file contains a specific string.
     *
     * @param  string  $path
     * @param  string  $content
     *
     * @return bool
     */
    public function contains( string $path, string $content ): bool;

    /**
     * Add content to a file.
     *
     * @param  string  $path
     * @param  string  $content
     *
     * @return int The number of bytes that were written to the file.
     */
    public function append_content( string $path, string $content ): int;

    /**
     * Remove content from a file.
     *
     * @param  string  $path
     * @param  string  $content
     *
     * @return mixed
     */
    public function remove_content( string $path, string $content );

    /**
     * Replace content in a file.
     *
     * @param  string  $path
     * @param  string  $search
     * @param  string  $replace
     *
     * @return mixed
     */
    public function replace_content( string $path, string $search, string $replace );

}
