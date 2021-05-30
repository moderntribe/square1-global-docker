<?php declare(strict_types=1);

namespace App\Commands\Settings;

use App\Commands\BaseCommand;
use App\Services\Settings\Groups\AllSettings;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

/**
 * The Settings Command.
 *
 * @package App\Commands\Settings
 */
class Settings extends BaseCommand {

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'settings
                            {--secrets : Show sensitive data}
                            {--reset : Reset settings to the default}';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Displays your current settings file';

	protected AllSettings $settings;

	public function __construct( AllSettings $settings ) {
		parent::__construct();

		$this->settings = $settings;
	}

	/**
	 * Execute the console command.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem  $filesystem
	 *
	 * @return int
	 */
	public function handle( Filesystem $filesystem ): int {
		$reset   = $this->option( 'reset' );
		$secrets = $this->option( 'secrets' );

		if ( $reset ) {
			$confirm = $this->confirm( 'Are you sure you want to reset your settings to the defaults?' );

			if ( ! $confirm ) {
				$this->info( 'Cancelled' );

				return self::EXIT_SUCCESS;
			}

			$filesystem->delete( $this->settings->writer()->file() );

			$this->info( 'Settings reset' );
		}

		$this->info( sprintf( 'File Location: %s', $this->settings->writer()->file() ) );
		$this->table( [ 'Setting', 'Value', 'Command' ], $this->formattedSettings( $secrets ) );

		return self::EXIT_SUCCESS;
	}

	protected function formattedSettings( bool $showSecrets ): array {
		$settings = Arr::dot( $this->settings->toArray() );

		$formatted = [];

		foreach ( $settings as $setting => $value ) {
			if ( is_bool( $value ) ) {
				$value = $value === false ? 'false' : 'true';
			}

			$formatted[] = [
				$setting,
				( ! $showSecrets && str_contains( $setting, 'secrets' ) ) ? '*********' : $value,
				str_contains( $setting, 'secrets' ) ? '' : 'so settings:' . str_replace( '.', ':', $setting ),
			];
		}

		return $formatted;
	}

}
