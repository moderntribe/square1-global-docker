<?php declare(strict_types=1);

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local docker restart command
 *
 * @package App\Commands\LocalDocker
 */
class Restart extends BaseLocalDocker {

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'restart';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Restarts your local SquareOne project';

	/**
	 * Execute the console command.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 *
	 * @return void
	 */
	public function handle( Config $config ): void {
		$this->info( sprintf( '➜ Restarting project %s...', $config->getProjectName() ) );

		chdir( $config->getDockerDir() );

		Artisan::call( DockerCompose::class, [
			'--project-name',
			$config->getProjectName(),
			'restart',
		] );

		$this->info( 'Done.' );
	}

}
