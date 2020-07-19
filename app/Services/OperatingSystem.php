<?php declare( strict_types=1 );

namespace App\Services;

use M1\Env\Parser;

/**
 * Class OperatingSystem
 *
 * @package App\Services
 */
class OperatingSystem {

    public const OS_RELEASE = '/etc/os-release';

    public const MAC_OS  = 'Darwin';
    public const LINUX   = 'Linux';
    public const ARCH    = 'Arch';
    public const DEBIAN  = 'Debian';
    public const MANJARO = 'Manjaro';
    public const UBUNTU  = 'Ubuntu';
    public const ZORIN   = 'Zorin';

    /**
     * Get the Operating System Family
     *
     * @codeCoverageIgnore
     *
     * @return string The OS Family
     */
    public function getFamily(): string {
        return PHP_OS_FAMILY;
    }

    /**
     * Detect a specific Linux flavor
     *
     * @return string The Linux flavor
     */
    public function getLinuxFlavor(): string {
        $flavor = $this->readOsRelease();

        if ( ! empty( $flavor ) ) {
            return $flavor;
        }

        $release = $this->getLsbRelease();

        $flavors = [
            self::ARCH,
            self::DEBIAN,
            self::MANJARO,
            self::UBUNTU,
            self::ZORIN,
        ];

        $flavor = array_filter( $flavors, static function ( $flavor ) use ( $release ) {
            return ( strpos( $release, $flavor ) !== false );
        } );

        return is_array( $flavor ) ? (string) current( $flavor ) : '';
    }

    /**
     * Read the OS release from the /etc/os-release file
     *
     * @codeCoverageIgnore
     *
     * @return string The Linux Flavor.
     */
    protected function readOsRelease(): string {
        if ( is_readable( self::OS_RELEASE ) ) {
            $release = Parser::parse( file_get_contents( self::OS_RELEASE ) );

            if ( ! empty( $release['ID_LIKE'] ) ) {
                return ucfirst( $release['ID_LIKE'] );
            }
        }

        return '';
    }

    /**
     * Fallback to use lsb_release -a
     *
     * @codeCoverageIgnore
     *
     * @return string|null
     */
    protected function getLsbRelease(): ?string {
        return shell_exec( 'lsb_release -a' );
    }

}
