<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local docker logs command
 *
 * @package App\Commands\LocalDocker
 */
class Logs extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logs';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Displays local SquareOne project docker logs';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     *
     * @return void
     */
    public function handle( Config $config ): void {
        $this->info( sprintf( '➜ Displaying logs for %s. Press command/ctrl + c to quit', $config->getProjectName() ) );

        chdir( $config->getDockerDir() );

        Artisan::call( DockerCompose::class, [
            '--project-name',
            $config->getProjectName(),
            'logs',
            '-f',
        ] );
    }

}
