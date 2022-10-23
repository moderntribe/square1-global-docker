<?php declare(strict_types=1);

namespace App\Commands\LocalDocker;

use App\Commands\Docker;
use App\Services\Docker\Container;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

class ExportTestDb extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'export-test-db {--output-path=/application/www/dev/tests/tests/_data/dump.sql : Where to export the database in the docker container}
                                           {--container=php-tests : The docker container to use}';
    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Export an updated test database after an WordPress upgrade';


    public function handle( Config $config, Container $container ) {
        $this->info( sprintf( '➜ Performing "wp core update-db" on test database for project %s...', $config->getProjectName() ) );

        Artisan::call( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--workdir',
            $config->getWorkdir(),
            $container->getId( $this->option( 'container' ) ),
            'wp',
            'core',
            'update-db',
        ] );

        $this->info( sprintf( '➜ Exporting test database for project %s to %s...', $config->getProjectName(), $this->option( 'output-path' ) ) );

        Artisan::call( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--workdir',
            $config->getWorkdir(),
            $container->getId( $this->option( 'container' ) ),
            'wp',
            'db',
            'export',
            '--add-drop-table',
            $this->option( 'output-path' ),
        ] );
    }

}
