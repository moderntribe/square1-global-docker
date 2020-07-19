<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local wp cli docker commands
 *
 * @package App\Commands\LocalDocker
 */
class Wp extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'wp {args* : arguments passed to the wp binary}
                           {--x|xdebug : Enable xdebug}
                           {--notty : Disable interactive/tty to capture output}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run WP CLI commands in the SquareOne local container';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     *
     * @return int
     */
    public function handle( Config $config ) {
        $params = [
            '--project-name',
            $config->getProjectName(),
            '--file',
            $config->getComposeFile(),
            'exec',
        ];

        if ( $this->option( 'notty' ) ) {
            $params = array_merge( $params, [ '-T' ] );
        }

        if ( $this->option( 'xdebug' ) ) {
            $env = [
                '--env',
                "PHP_IDE_CONFIG=serverName={$config->getProjectName()}.tribe",
            ];

            $exec = [
                'php',
                '-dxdebug.remote_autostart=1',
                '-dxdebug.remote_host=host.tribe',
                '-dxdebug.remote_enable=1',
                '/usr/local/bin/wp',
                '--allow-root',
            ];
        } else {
            $env = [
                '--env',
                'WP_CLI_PHP_ARGS',
            ];

            $exec = [
                '/usr/local/bin/wp',
                '--allow-root',
            ];
        }

        $params = array_merge( $params, $env, [ 'php-fpm' ], $exec, $this->argument( 'args' ) );

        return Artisan::call( DockerCompose::class, $params );
    }

}
