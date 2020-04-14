<?php declare( strict_types=1 );

namespace Tribe\Sq1\Models;

/**
 * Operating System Model.
 *
 * @package Tribe\Sq1\Models
 */
class OperatingSystem {

	public const MAC_OS  = 'Darwin';
	public const LINUX   = 'Linux';
	public const WINDOWS = 'Windows';
	public const ARCH    = 'Arch';
	public const DEBIAN  = 'Debian';
	public const MANJARO = 'Manjaro';
	public const UBUNTU  = 'Ubuntu';

	/**
	 * Get the Operating System Family
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
		$release = shell_exec( 'lsb_release -d' );

		$flavors = [
			self::ARCH,
			self::DEBIAN,
			self::MANJARO,
			self::UBUNTU,
		];

		$flavor = array_filter( $flavors, function ( $flavor ) use ( $release ) {
			return ( strpos( $release, $flavor ) !== false );
		} );

		return is_array( $flavor ) ? current( $flavor ) : '';
	}

}
