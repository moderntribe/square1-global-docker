<?php declare(strict_types=1);

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use App\Services\XdebugValidator;
use App\Traits\XdebugWarningTrait;
use Illuminate\Support\Facades\Artisan;

/**
 * Local wp cli docker commands
 *
 * @package App\Commands\LocalDocker
 */
class Wp extends BaseLocalDocker {

	use XdebugWarningTrait;

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'wp {args* : arguments passed to the wp binary}
                           {--x|xdebug : Enable xdebug}
                           {--notty : Disable interactive/tty to capture output}';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Run WP CLI commands in the SquareOne local container';

	/**
	 * Execute the console command.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 * @param  \App\Services\XdebugValidator      $xdebugValidator
	 *
	 * @return int|null
	 */
	public function handle( Config $config, XdebugValidator $xdebugValidator ): ?int {
		$params = [
			'--project-name',
			$config->getProjectName(),
			'exec',
		];

		if ( $this->option( 'notty' ) ) {
			$params = array_merge( $params, [ '-T' ] );
		}

		if ( $this->option( 'xdebug' ) ) {
			$phpIni = $config->getPhpIni();

			if ( ! $xdebugValidator->valid( $phpIni ) ) {
				$this->outdatedXdebugWarning( $phpIni );
			}

			$env = [
				'--env',
				self::XDEBUG_ENV,
			];
		} else {
			$env = [
				'--env',
				'WP_CLI_PHP_ARGS',
			];
		}

		$exec = [
			'/usr/local/bin/wp',
			'--allow-root',
		];

		$params = array_merge( $params, $env, [ 'php-fpm' ], $exec, $this->argument( 'args' ) );

		chdir( $config->getDockerDir() );

		return Artisan::call( DockerCompose::class, $params );
	}

}
