<?php declare( strict_types=1 );

namespace App\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use Illuminate\Support\Facades\Artisan;

/**
 * Global docker logs command
 *
 * @package App\Commands\GlobalDocker
 */
class Logs extends BaseGlobalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'global:logs';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Displays SquareOne global docker logs';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void {

        Artisan::call( DockerCompose::class, [
            '--project-name',
            self::PROJECT_NAME,
            '--file',
            $this->dockerComposeFile,
            'logs',
            '-f',
        ] );
    }

}
