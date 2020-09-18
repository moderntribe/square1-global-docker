<?php declare( strict_types=1 );

namespace App\Services;

/**
 * Terminates Script Execution
 *
 * @codeCoverageIgnore
 *
 * @package App\Services
 */
class Terminator {

    /**
     * Terminate the script with exit
     *
     * @param  string  $message
     */
    public function exit( string $message ): void {
        exit( $message );
    }
}
