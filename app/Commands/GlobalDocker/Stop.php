<?php declare( strict_types=1 );

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
     */
    protected $signature = 'global:stop';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Stops the SquareOne global docker containers';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void {
        $this->info( '➜ Stopping global docker containers...' );

        Artisan::call( DockerCompose::class, [
            '--project-name',
            self::PROJECT_NAME,
            '--file',
            $this->dockerComposeFile,
            'down',
        ] );

        $this->info( 'Done.' );
    }

}
