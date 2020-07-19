<?php declare( strict_types=1 );

namespace App\Services;

/**
 * Class HomeDir
 *
 * @package App\Services
 */
class HomeDir {

    /**
     * Get a user's home directory.
     *
     * @note Taken from Drush.
     *
     * @return string|null
     */
    public function get(): ?string {
        // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = getenv( 'HOME' );

        if ( ! empty( $home ) ) {
            // home should never end with a trailing slash.
            $home = rtrim( $home, '/' );
        } elseif ( ! empty( $_SERVER['HOMEDRIVE'] ) && ! empty( $_SERVER['HOMEPATH'] ) ) {
            // home on windows
            $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
            // If HOMEPATH is a root directory the path can end with a slash. Make sure
            // that doesn't happen.
            $home = rtrim( $home, '\\/' );
        }

        return empty( $home ) ? null : $home;
    }
}
