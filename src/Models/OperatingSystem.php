<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Models;

/**
 * Operating System Model.
 *
 * @package Tribe\SquareOne\Models
 */
class OperatingSystem {

	public const MAC_OS  = 'Darwin';
	public const LINUX   = 'Linux';
	public const WINDOWS = 'Windows';
	public const ARCH    = 'Arch';
	public const DEBIAN  = 'Debian';
	public const MANJARO = 'Manjaro';
	public const UBUNTU  = 'Ubuntu';
	public const ZORIN   = 'Zorin';

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
			self::ZORIN,
		];

		$flavor = array_filter( $flavors, static function ( $flavor ) use ( $release ) {
			return ( strpos( $release, $flavor ) !== false );
		} );

		return (string) is_array( $flavor ) ? current( $flavor ) : '';
	}

}
