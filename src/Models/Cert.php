<?php declare( strict_types=1 );

namespace Tribe\Sq1\Models;

/**
 * SSL Certificate
 *
 * @package Tribe\Sq1\Models
 */
class Cert {

	/**
	 * The certificate path.
	 *
	 * @var false|string
	 */
	protected $certPath;

	public function __construct( string $path ) {
		$this->certPath = realpath( $path );
	}

	/**
	 * Check if a certificate exists.
	 *
	 * @return bool
	 */
	public function exists(): bool {
		return ( ! empty( $this->certPath ) && is_file( $this->certPath ) );
	}

	/**
	 * Check if a certificate is expired.
	 *
	 * @return bool
	 */
	public function expired(): bool {
		$info = openssl_x509_parse( file_get_contents( $this->certPath ) );

		return ( ! empty( $info['validTo_time_t'] ) && $info['validTo_time_t'] < time() );
	}

}
