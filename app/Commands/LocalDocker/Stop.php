<?php declare( strict_types=1 );

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
     */
    protected $signature = 'stop';

    /**
     * The description of the command.
     *
     * @var string
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

        Artisan::call( DockerCompose::class, [
            '--project-name',
            $config->getProjectName(),
            '--file',
            $config->getComposeFile(),
            'down',
        ] );

        $this->info( 'Done.' );
    }
}
