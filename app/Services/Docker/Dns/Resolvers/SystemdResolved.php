<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns\Resolvers;

use LaravelZero\Framework\Commands\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

/**
 * Class SystemdResolved
 *
 * @package App\Services\Docker\Dns\Resolvers
 */
class SystemdResolved extends BaseResolver {

    /**
     * Test if systemd-resolved is active.
     *
     * @return bool
     */
    public function supported(): bool {
        $response = $this->runner->run( 'systemctl status systemd-resolved' );

        if ( $response->ok() ) {
            if ( str_contains( (string) $response, 'active (running)' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test if this resolver is already enabled.
     *
     * @return bool
     */
    public function enabled(): bool {
        try {
            $content = $this->filesystem->get( '/etc/systemd/resolved.conf' );

            if ( str_contains( $content, 'DNS=127.0.0.1' ) ) {
                return true;
            }

        } catch ( FileNotFoundException $exception ) {
            return false;
        }

        return false;
    }

    /**
     * Enable resolver for systemd-resolved strategy.
     *
     * @param  \LaravelZero\Framework\Commands\Command  $command
     */
    public function enable( Command $command ): void {
        $command->task( '<comment>➜ Copying custom /etc/systemd/resolved.conf</comment>', call_user_func( [ $this, 'copyResolvedConf' ] ) );
        $command->task( '<comment>➜ Symlinking /run/systemd/resolve/resolv.conf /etc/resolv.conf</comment>', call_user_func( [ $this, 'symlinkResolvConf' ] ) );
        $command->task( '<comment>➜ Restarting systemd-resolved</comment>', call_user_func( [ $this, 'restartSystemdResolved' ] ) );
    }

    /**
     * Copy our custom resolved.conf to /etc/systemd
     *
     */
    public function copyResolvedConf(): void {
        $this->runner->with( [
            'custom_resolved_conf' => storage_path( 'dns/debian/resolved.conf' ),
            'system_resolved_conf' => '/etc/systemd/resolved.conf',
        ] )->run( 'sudo cp {{ $custom_resolved_conf }} {{ $system_resolved_conf }}' )->throw();
    }

    /**
     * Symlink resolv.conf to /run/systemd/resolve/resolv.conf to remove stub-resolv.conf.
     *
     */
    public function symlinkResolvConf(): void {
        $this->runner->run( 'sudo ln -fsn /run/systemd/resolve/resolv.conf /etc/resolv.conf' )->throw();
    }

    /**
     * Restart systemd-resolved.
     *
     */
    public function restartSystemdResolved(): void {
        $this->runner->run( 'sudo systemctl restart systemd-resolved' )->throw();
    }

}
