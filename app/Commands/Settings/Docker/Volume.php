<?php declare(strict_types=1);

namespace App\Commands\Settings\Docker;

use App\Commands\BaseCommand;
use App\Services\Docker\Volumes\VolumeCollection;
use App\Services\OperatingSystem;
use App\Services\Settings\Groups\AllSettings;
use App\Services\Settings\Groups\Docker;

/**
 * Docker Volume Settings.
 *
 * @package App\Commands\Settings\Docker
 */
class Volume extends BaseCommand {

	public const OPTION_RESET = 'reset';

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'settings:docker:volume';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Manage your docker volume type';

	protected AllSettings $settings;

	public function __construct( AllSettings $settings ) {
		parent::__construct();

		$this->settings = $settings;
	}

	/**
	 * Execute the console command.
	 *
	 * @param \App\Services\OperatingSystem                 $os
	 * @param \App\Services\Docker\Volumes\VolumeCollection $volumes
	 *
	 * @return int
	 */
	public function handle( OperatingSystem $os, VolumeCollection $volumes ): int {
		$currentVolume = $this->settings->docker->volume;

		$options = [
			Docker::BIND => 'Bind (recommended): The default docker volume. 100% performance on Linux',
			Docker::NONE => 'None: All files live in the container and are not shared with the host. Work is LOST once the container is stopped',
			'reset'      => 'Reset: Resets any changes to the operating system based on previous selections',
		];

		if ( OperatingSystem::MAC_OS === $os->getFamily() ) {
			$options = array_merge( $options, [
				Docker::NFS     => 'NFS (recommended): Use a Network File Share, up to a 60% performance boost on macOS',
				Docker::MUTAGEN => 'Mutagen (experimental): Highly experimental file sync using Mutagen Compose',
			] );


			// Remove bind recommendation on macOS
			$options[ Docker::BIND ] = str_replace( ' (recommended)', '', $options[ Docker::BIND ] );
		}

		asort( $options );

		$menu = $this->menu( sprintf( 'Select a Docker Volume Type [Current: %s]', ucwords( $currentVolume ) ), $options );
		$menu->setExitButtonText( 'Cancel' );

		$option = $menu->open();

		if ( $option ) {
			// Reset to default
			if ( self::OPTION_RESET === $option ) {
				return $this->resetVolume( $volumes );
			}

			/** @var \App\Contracts\Volume $volume */
			$volume = $volumes->collection()->get( $option );

			if ( ! $volume ) {
				$this->error( sprintf( 'Invalid docker volume selected: %s', $option ) );

				return self::EXIT_ERROR;
			}

			if ( $volume->enabled( $option ) ) {
				$this->warn( sprintf( 'Docker volume "%s" is already enabled', $option ) );

				return self::EXIT_ERROR;
			}

			if ( ! $volume->enable( $this )  ) {
				$this->error( sprintf( 'Unable to enable volume type: %s', $option ) );

				return self::EXIT_ERROR;
			}

			$this->settings->docker->volume = $option;
			$this->settings->save();
		}

		$this->info( sprintf( 'Current docker volume type: %s', $this->settings->docker->volume ) );

		return self::EXIT_SUCCESS;
	}

	/**
	 * Reset to default docker volume, removing configuration for all other types.
	 *
	 * @param \App\Services\Docker\Volumes\VolumeCollection $volumes
	 *
	 * @return int
	 */
	protected function resetVolume( VolumeCollection $volumes ): int {
		$confirm = $this->confirm( 'Are you sure you want to reset all docker volumes back to Bind?' );

		if ( ! $confirm ) {
			$this->info( 'Cancelled' );

			return self::EXIT_SUCCESS;
		}

		$volumes->collection()->each( static function ( \App\Contracts\Volume $volume ): void {
			$volume->remove();
		} );

		$this->settings->docker->volume = Docker::BIND;
		$this->settings->save();

		$this->info( 'Docker volume reset to Bind' );

		return self::EXIT_SUCCESS;
	}

}
