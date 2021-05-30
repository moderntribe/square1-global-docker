<?php declare(strict_types=1);

namespace App\Services\Docker\Dns\Resolvers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use LaravelZero\Framework\Commands\Command;

/**
 * Class Dhcp
 *
 * @package App\Services\Docker\Dns\Resolvers
 */
class Dhcp extends BaseResolver {

	/**
	 * Test whether NetworkManager is active.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		$response = $this->runner->run( 'systemctl status NetworkManager' );

		if ( $response->ok() ) {
			if ( str_contains( (string) $response, 'active (running)' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Test if DNS for DHCP is already enabled.
	 *
	 * @return bool
	 */
	public function enabled(): bool {
		try {
			$content = $this->filesystem->get( '/etc/dhcp/dhclient.conf' );

			if ( str_contains( $content, 'prepend domain-name-servers 127.0.0.1;' ) ) {
				return true;
			}
		} catch ( FileNotFoundException $exception ) {
			return false;
		}

		return false;
	}

	/**
	 * Enable DNS for DHCP.
	 *
	 * @param  \LaravelZero\Framework\Commands\Command  $command
	 */
	public function enable( Command $command ): void {
		if ( ! $this->filesystem->exists( '/etc/dhcp' ) ) {
			$command->task( '<comment>➜ Creating folder /etc/dhcp</comment>', call_user_func( [ $this, 'createFolder' ] ) );
		}

		$command->task( '<comment>➜ Adding 127.0.0.1 nameservers to /etc/dhcp/dhclient.conf</comment>', call_user_func( [ $this, 'addNameservers' ] ) );

		$command->task( '<comment>➜ Restarting NetworkManager</comment>', call_user_func( [ $this, 'restartNetworkManager' ] ) );
	}

	/**
	 * Create the /etc/dhcp folder.
	 */
	public function createFolder(): void {
		$this->runner->run( 'sudo mkdir /etc/dhcp' )->throw();
	}

	/**
	 * Add nameservers to /etc/dhcp/dhclient.conf
	 */
	public function addNameservers(): void {
		$this->runner->run( 'echo "prepend domain-name-servers 127.0.0.1;" | sudo tee -a /etc/dhcp/dhclient.conf' )->throw();
	}

	/**
	 * Restart network manager.
	 */
	public function restartNetworkManager(): void {
		$this->runner->run( 'sudo systemctl restart NetworkManager' )->throw();
	}

}
