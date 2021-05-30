<?php declare(strict_types=1);

namespace App\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use Illuminate\Support\Facades\Artisan;

/**
 * Global docker stop command
 *
 * @package App\Commands\GlobalDocker
 */
class Stop extends BaseGlobalDocker {

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'global:stop';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Stops the SquareOne global docker containers';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(): void {
		$this->info( '➜ Stopping global docker containers...' );

		chdir( $this->globalDirectory );

		Artisan::call( DockerCompose::class, [
			'--project-name',
			self::PROJECT_NAME,
			'down',
		] );

		$this->info( 'Done.' );
	}

}
