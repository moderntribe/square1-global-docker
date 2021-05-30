<?php declare(strict_types=1);

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local docker shell command
 *
 * @package App\Commands\LocalDocker
 */
class Shell extends BaseLocalDocker {

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'shell {--user=squareone : The username or UID of the account to use}';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Gives you a bash shell into the php-fpm docker container';

	/**
	 * Execute the console command.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 */
	public function handle( Config $config ): void {
		$this->info( sprintf( '➜ Launching shell for %s...', $config->getProjectName() ) );

		chdir( $config->getDockerDir() );

		$result = Artisan::call( DockerCompose::class, [
			'--project-name',
			$config->getProjectName(),
			'exec',
			'--user',
			$this->option( 'user' ),
			'php-fpm',
			'/bin/bash',
		] );

		if ( self::EXIT_ERROR !== $result ) {
			return;
		}

		$this->error( 'Whoops! This project is using an older php-fpm container. Try running "so shell --user root" instead' );
	}

}
