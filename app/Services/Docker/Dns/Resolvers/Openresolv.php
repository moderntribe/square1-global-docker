<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns\Resolvers;

use LaravelZero\Framework\Commands\Command;

/**
 * Set resolvers for systems using the resolvconf package.
 *
 * @see     https://packages.ubuntu.com/focal/resolvconf
 *
 * @package App\Services\Docker\Dns\Resolvers
 */
class Openresolv extends ResolvConf {

    /**
     * Check if openresolv's resolvconf binary is available.
     *
     * @see https://roy.marples.name/projects/openresolv/
     *
     * @return bool
     */
    public function supported(): bool {
        $response = $this->runner->run( 'resolvconf --version' );

        if ( $response->ok() ) {
            if ( str_contains( (string) $response, 'openresolv' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enable local nameservers for /etc/resolv.conf.
     *
     * @param   \LaravelZero\Framework\Commands\Command  $command
     */
    public function enable( Command $command ): void {
        parent::enable( $command );

        $command->task( '<comment>➜ Running sudo resolvconf -u</comment>', call_user_func( [
            $this,
            'writeResolvConf'
        ] ) );
    }

    /**
     * Write to /etc/resolv.conf
     */
    public function writeResolvConf(): void {
        $this->runner->run( 'sudo resolvconf -u' )->throw();
    }

}
