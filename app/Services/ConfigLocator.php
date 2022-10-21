<?php declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Config\FileLocator;
use Throwable;

class ConfigLocator {

    /**
     * Find a configuration file and traverse up the file system until we
     * find one or reach the root.
     *
     * @param  string  $directory  The file system path to begin the search.
     * @param  string  $filename   The file to search for.
     *
     * @return string
     */
    public function find( string $directory, string $filename = 'squareone.yml' ): string {
        $found = '';

        while ( true ) {
            $locator = new FileLocator( $directory );

            try {
                $found = $locator->locate( $filename );
            } catch ( Throwable $e ) {
                $directory = dirname( $directory );
            }

            if ( $found || $directory === '/' ) {
                return $found;
            }
        }
    }

}
