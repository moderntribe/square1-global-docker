<?php declare(strict_types=1);

namespace App\Services\Certificate;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;

/**
 * Certificate Handler
 *
 * @package App\Services\Certificate
 */
class Handler {

	/**
	 * The CA certificate instance.
	 */
	protected Ca $ca;

	/**
	 * The local certificate instance.
	 */
	protected Certificate $certificate;

	protected Filesystem $filesystem;

	/**
	 * The path to where certificates are stored, generally: ~/USER/.config/squareone/global/certs.
	 */
	protected string $certFolder;

	/**
	 * The path to the CA Certificate.
	 */
	protected string $caPath;

	/**
	 * Handler constructor.
	 *
	 * @param  \App\Services\Certificate\Ca           $ca
	 * @param  \App\Services\Certificate\Certificate  $certificate
	 * @param  \Illuminate\Filesystem\Filesystem      $filesystem
	 * @param  string                                 $certFolder
	 */
	public function __construct( Ca $ca, Certificate $certificate, Filesystem $filesystem, string $certFolder ) {
		$this->ca          = $ca;
		$this->certificate = $certificate;
		$this->filesystem  = $filesystem;
		$this->certFolder  = $certFolder;
		$this->caPath      = $this->certFolder . '/' . Ca::PEM_NAME;
	}

	/**
	 * Check if a CA certificate already exists.
	 *
	 * @return bool
	 */
	public function caExists(): bool {
		return  $this->filesystem->exists( $this->caPath ) && $this->ca->installed();
	}

	/**
	 * Create a CA certificate
	 */
	public function createCa(): void {
		$this->ca->create( $this->certFolder );
	}

	/**
	 * Check if a certificate exists.
	 *
	 * @param  string  $domain  The domain name
	 *
	 * @return bool
	 */
	public function certificateExists( string $domain = '' ): bool {
		return $this->certificate->exists( $this->buildCertificatePath( $domain ) );
	}

	/**
	 * Create a local certificate if it doesn't exist or is expired.
	 *
	 * @param  string  $domain
	 */
	public function createCertificate( string $domain = '' ): void {
		if ( empty( $domain ) ) {
			throw new InvalidArgumentException( 'Cannot create a certificate with an empty domain' );
		}

		$file = $this->buildCertificatePath( $domain );

		if ( $this->certificateExists( $domain ) && ! $this->certificate->expired( $file ) ) {
			return;
		}

		$this->certificate->create( $domain, $this->certFolder );
	}

	/**
	 * Build a certificate path based on a domain name
	 *
	 * @param  string  $domain  The domain name
	 *
	 * @return string
	 */
	protected function buildCertificatePath( string $domain = '' ): string {
		return $this->certFolder . '/' . $domain . '.crt';
	}

}
