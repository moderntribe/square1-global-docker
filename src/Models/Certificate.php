<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Models;

/**
 * SSL Certificate Model
 *
 * @package Tribe\SquareOne\Models
 */
class Certificate {

	/**
	 * The path to the crt file.
	 *
	 * @var false|string
	 */
	protected $certPath;


	/**
	 * Certificate Path Setter
	 *
	 * @param  string  $path  The path to the .crt file.
	 *
	 * @return \Tribe\SquareOne\Models\Certificate
	 */
	public function setCertPath( string $path ): self {
		$this->certPath = realpath( $path );

		return $this;
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
