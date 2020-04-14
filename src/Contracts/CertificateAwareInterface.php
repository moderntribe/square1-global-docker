<?php declare( strict_types=1 );

namespace Tribe\Sq1\Contracts;

use Tribe\Sq1\Models\Certificate;

/**
 * Interface CertificateAwareInterface
 *
 * @package Tribe\Sq1\Contracts
 */
interface CertificateAwareInterface {

	/**
	 * Inject a Certificate model.
	 *
	 * @param  \Tribe\Sq1\Models\Certificate  $cert
	 *
	 * @return void
	 */
	public function setCertificate( Certificate $cert ): void;

	/**
	 * Get the Certificate model.
	 *
	 * @return \Tribe\Sq1\Models\Certificate
	 */
	public function getCertificate(): Certificate;
}
