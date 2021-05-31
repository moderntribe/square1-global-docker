<?php declare(strict_types=1);

namespace App\Services\Docker\Volumes\Types;

use App\Services\Docker\Local\Config;
use App\Services\Docker\Network;
use App\Services\Nfs\ExportsModifier;
use App\Services\Nfs\Nfsd;
use App\Services\OperatingSystem;
use App\Services\Settings\Groups\AllSettings;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * Network File Share Docker Volume.
 *
 * @package App\Services\Docker\Volumes
 */
class NfsVolume extends BaseVolume {

	protected ExportsModifier $modifier;
	protected Network $network;
	protected Nfsd $nfsd;
	protected Filesystem $filesystem;

	public function __construct( OperatingSystem $os, AllSettings $settings, ExportsModifier $modifier, Network $network, Nfsd $nfsd, Filesystem $filesystem ) {
		parent::__construct( $os, $settings );

		$this->modifier   = $modifier;
		$this->network    = $network;
		$this->nfsd       = $nfsd;
		$this->filesystem = $filesystem;
	}

	/**
	 * Supported only on macOS.
	 *
	 * @return bool
	 */
	public function supported(): bool {
		return $this->os->getFamily() === OperatingSystem::MAC_OS;
	}

	/**
	 * @param \LaravelZero\Framework\Commands\Command $command
	 *
	 * @return bool
	 */
	public function enable( Command $command ): bool {
		$path    = $command->ask( 'Enter the full path to the parent folder for your Tribe projects:', '/Users' );
		$confirm = $command->confirm( 'Alright, we\'re going to attempt to automatically configure your share. Enter your sudo password when requested. Ready?' );

		if ( ! $confirm ) {
			$command->error( 'Cancelled NFS creation' );

			return false;
		}

		if ( ! $this->filesystem->exists( $path ) ) {
			$command->error( sprintf( 'Unable to find path: %s', $path ) );

			return false;
		}

		try {
			$this->modifier->add( $path, $this->network->getGateWayIP(), Config::uid(), Config::gid() );
		} catch ( Throwable $e ) {
			$command->error( sprintf( 'Error: %s', $e->getMessage() ) );

			return false;
		}

		$this->nfsd->restart();

		return true;
	}

	/**
	 * @throws \App\Exceptions\SystemExitException
	 */
	public function remove(): void {
		$this->modifier->remove();
	}

}
