<?php declare(strict_types=1);

namespace App\Services\Certificate\Trust\Strategies;

use App\Services\Certificate\Trust\BaseTrust;

/**
 * Class MacOs
 *
 * @package App\Services\Certificate\Trust\Strategies
 */
class MacOs extends BaseTrust {

	/**
	 * Check if the CA certificate is installed on the host.
	 *
	 * @return bool
	 */
	public function installed(): bool {
		$result = $this->runner->run( 'security find-certificate -c tri.be' );

		return $result->ok();
	}

	/**
	 * Trust the given root certificate file in the Keychain.
	 *
	 * @param  string  $crt  The path to the crt file
	 */
	public function trustCa( string $crt ): void {
		$this->runner->with( [
			'crt' => $crt,
		] )->run( 'sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain {{ $crt }}' )
					 ->throw();
	}

	/**
	 * Trust the given certificate in the Mac keychain.
	 *
	 * @param  string  $crt
	 *
	 * @return void
	 */
	public function trustCertificate( string $crt ): void {
		$this->runner->with( [
			'crt' => $crt,
		] )->run( 'sudo security add-trusted-cert -d -r trustAsRoot -k /Library/Keychains/System.keychain {{ $crt }}' )->throw();
	}

}
