<?php declare( strict_types=1 );

namespace App\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use Illuminate\Support\Facades\Artisan;

/**
 * Global docker restart command
 *
 * @package App\Commands\GlobalDocker
 */
class Restart extends BaseGlobalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'global:restart';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Restarts the SquareOne global docker containers';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void {
        $this->info( '➜ Restarting global docker containers...' );

        chdir( $this->globalDirectory );

        Artisan::call( DockerCompose::class, [
            '--project-name',
            self::PROJECT_NAME,
            'restart',
        ] );

        $this->info( 'Done.' );
    }
}
