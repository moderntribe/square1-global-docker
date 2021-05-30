<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\Resolvers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use LaravelZero\Framework\Commands\Command;

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

			if ( str_contains( $content, 'DNS=127.0.0.1' ) && str_contains( $content, 'DNSStubListener=no' ) ) {
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
		$command->task( '<comment>➜ Backing up /etc/systemd/resolved.conf</comment>', call_user_func( [ $this, 'backupResolvedConf' ] ) );
		$command->task( '<comment>➜ Copying custom /etc/systemd/resolved.conf</comment>', call_user_func( [ $this, 'copyResolvedConf' ] ) );
		$command->task( '<comment>➜ Symlinking /run/systemd/resolve/resolv.conf /etc/resolv.conf</comment>', call_user_func( [ $this, 'symlinkResolvConf' ] ) );
		$command->task( '<comment>➜ Restarting systemd-resolved</comment>', call_user_func( [ $this, 'restartSystemdResolved' ] ) );
	}

	/**
	 * Back up an existing resolved.conf
	 */
	public function backupResolvedConf(): void {
		$this->runner->with( [
			'date'                 => date( 'Ymdis' ),
			'system_resolved_conf' => '/etc/systemd/resolved.conf',
		] )->run( 'sudo cp {{ $system_resolved_conf }} {{ $system_resolved_conf }}.backup.{{ $date }}' )->throw();
	}

	/**
	 * Copy our custom resolved.conf to /etc/systemd
	 */
	public function copyResolvedConf(): void {
		$temp_file = tempnam( '/tmp', 'sq1resolved' );

		$this->filesystem->replace( $temp_file, $this->filesystem->get( storage_path( 'dns/debian/resolved.conf' ) ) );

		$this->runner->with( [
			'temp_resolved_conf'   => $temp_file,
			'system_resolved_conf' => '/etc/systemd/resolved.conf',
		] )->run( 'sudo cp -f {{ $temp_resolved_conf }} {{ $system_resolved_conf }}' )->throw();

		$this->filesystem->delete( $temp_file );
	}

	/**
	 * Symlink resolv.conf to /run/systemd/resolve/resolv.conf to remove stub-resolv.conf.
	 */
	public function symlinkResolvConf(): void {
		$this->runner->run( 'sudo ln -fsn /run/systemd/resolve/resolv.conf /etc/resolv.conf' )->throw();
	}

	/**
	 * Restart systemd-resolved.
	 */
	public function restartSystemdResolved(): void {
		$this->runner->run( 'sudo systemctl restart systemd-resolved' )->throw();
	}

}
