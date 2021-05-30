<?php declare(strict_types=1);

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local docker stop command
 *
 * @package App\Commands\LocalDocker
 */
class Stop extends BaseLocalDocker {

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'stop {--remove-orphans : Remove containers for services not in the compose file}';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Stops your local SquareOne project, run anywhere in a project folder';

	/**
	 * Execute the console command.
	 *
	 * @param  \App\Services\Docker\Local\Config  $config
	 *
	 * @return void
	 */
	public function handle( Config $config ): void {
		$this->info( sprintf( '➜ Stopping project %s...', $config->getProjectName() ) );

		chdir( $config->getDockerDir() );

		$args = [
			'--project-name',
			$config->getProjectName(),
			'down',
		];

		if ( $this->option( 'remove-orphans' ) ) {
			$args[] = '--remove-orphans';
		}

		Artisan::call( DockerCompose::class, $args );

		$this->info( 'Done.' );
	}

}
