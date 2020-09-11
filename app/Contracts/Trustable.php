<?php declare( strict_types=1 );

namespace App\Contracts;

/**
 * Interface Trustable
 *
 * @package App\Services\Certificate\Strategies
 */
interface Trustable {

    /**
     * Whether the certificate is already installed on the host system.
     *
     * @return bool
     */
    public function installed(): bool;

    /**
     * Run the commands in order to trust a CA certificate
     *
     * @param  string  $crt  The path to the crt file
     *
     * @return mixed
     */
    public function trustCa( string $crt );

    /**
     * Run the commands in order to trust a certificate
     *
     * @param  string  $crt  The path to the crt file
     *
     * @return mixed
     */
    public function trustCertificate( string $crt );

}
