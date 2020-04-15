<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Contracts;

use Tribe\SquareOne\Models\Certificate;

/**
 * Interface CertificateAwareInterface
 *
 * @package Tribe\SquareOne\Contracts
 */
interface CertificateAwareInterface {

	/**
	 * Inject a Certificate model.
	 *
	 * @param  \Tribe\SquareOne\Models\Certificate  $cert
	 *
	 * @return void
	 */
	public function setCertificate( Certificate $cert ): void;

	/**
	 * Get the Certificate model.
	 *
	 * @return \Tribe\SquareOne\Models\Certificate
	 */
	public function getCertificate(): Certificate;
}
