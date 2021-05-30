<?php declare(strict_types=1);

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Enable/Disable Xdebug in the php-fpm container.
 *
 * @package App\Commands\LocalDocker
 */
class Xdebug extends BaseLocalDocker {

	/**
	 * The path to the xdebug.ini in the container.
	 */
	public const XDEBUG_CONFIG_PATH = '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini';

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'xdebug {action? : on|off}';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Enable/disable Xdebug in the php-fpm container to increase performance on MacOS';

	/**
	 * Execute the console command.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 *
	 * @return int
	 */
	public function handle( Config $config ): int {
		$action = $this->argument( 'action' );

		chdir( $config->getDockerDir() );

		if ( empty( $action ) ) {
			$result = Artisan::call( DockerCompose::class, [
				'--project-name',
				$config->getProjectName(),
				'exec',
				'--user',
				'root',
				'php-fpm',
				'bash',
				'-c',
				sprintf( '[[ -f %s ]]', self::XDEBUG_CONFIG_PATH ),
			] );

			if ( self::EXIT_SUCCESS === $result ) {
				$this->info( 'xdebug is on' );
			} else {
				$this->info( 'xdebug is off' );
			}

			return self::EXIT_SUCCESS;
		}

		if ( 'on' === $action ) {
			$this->enable( $config );
			$this->reload( $config );
			$this->info( 'xdebug enabled' );
		} elseif ( 'off' === $action ) {
			$this->disable( $config );
			$this->reload( $config );
			$this->info( 'xdebug disabled' );
		} else {
			$this->error( sprintf( 'Invalid argument: %s. Allowed values: on|off', $action ) );
		}

		return self::EXIT_SUCCESS;
	}

	/**
	 * Enable xdebug by renaming the .ini file.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 */
	protected function enable( Config $config ): void {
		Artisan::call( DockerCompose::class, [
			'--project-name',
			$config->getProjectName(),
			'exec',
			'-T',
			'--user',
			'root',
			'php-fpm',
			'mv',
			sprintf( '%s', self::XDEBUG_CONFIG_PATH . '.disabled' ),
			sprintf( '%s', self::XDEBUG_CONFIG_PATH ),
		] );
	}

	/**
	 * Disable xdebug by renaming the .ini file back.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 */
	protected function disable( Config $config ): void {
		Artisan::call( DockerCompose::class, [
			'--project-name',
			$config->getProjectName(),
			'exec',
			'-T',
			'--user',
			'root',
			'php-fpm',
			'mv',
			sprintf( '%s', self::XDEBUG_CONFIG_PATH ),
			sprintf( '%s', self::XDEBUG_CONFIG_PATH . '.disabled' ),
		] );
	}

	/**
	 * Reload PHP in the container.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 */
	protected function reload( Config $config ): void {
		Artisan::call( DockerCompose::class, [
			'--project-name',
			$config->getProjectName(),
			'exec',
			'--user',
			'root',
			'php-fpm',
			'kill',
			'-USR2',
			'1',
		] );
	}

}
