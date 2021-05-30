<?php declare(strict_types=1);

namespace App\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Dns\Handler;
use Illuminate\Support\Facades\Artisan;

/**
 * Global docker start command
 *
 * @package App\Commands\GlobalDocker
 */
class Start extends BaseGlobalDocker {

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'global:start';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Starts the SquareOne global docker containers';

	/**
	 * Execute the console command.
	 *
	 * @param  \App\Services\Docker\Dns\Handler  $resolveHandler
	 */
	public function handle( Handler $resolveHandler ): void {
		$this->info( '➜ Starting global docker containers...' );

		if ( ! $resolveHandler->enabled() ) {
			$this->error( 'DNS resolvers not enabled! Enter your sudo password when requested to enable them...' );
			$this->enableDnsResolvers( $resolveHandler );
		}

		chdir( $this->globalDirectory );

		Artisan::call( DockerCompose::class, [
			'--project-name',
			self::PROJECT_NAME,
			'up',
			'--remove-orphans',
			'-d',
		] );

		$this->info( 'Done.' );
	}

	/**
	 * Write the nameservers to the relevant resolvers.
	 *
	 * @param  \App\Services\Docker\Dns\Handler  $resolveHandler
	 */
	protected function enableDnsResolvers( Handler $resolveHandler ): void {
		$resolveHandler->enable( $this );
	}

}
