<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\Docker;
use App\Services\Docker\Container;
use App\Services\XdebugValidator;
use App\Traits\XdebugWarningTrait;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local wp cli docker commands
 *
 * @package App\Commands\LocalDocker
 */
class Wp extends BaseLocalDocker {

    use XdebugWarningTrait;

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
     * @param  \App\Services\XdebugValidator      $xdebugValidator
     * @param  \App\Services\Docker\Container     $container
     *
     * @return int|null
     */
    public function handle( Config $config, XdebugValidator $xdebugValidator, Container $container ): ?int {
        $params = [
            'exec',
            ! $this->option( 'notty' ) ? '--tty' : '',
            '-w',
            $config->getWorkdir(),
        ];

        if ( $this->option( 'xdebug' ) ) {

            $phpIni = $config->getPhpIni();

            if ( ! $xdebugValidator->valid( $phpIni ) ) {
                $this->outdatedXdebugWarning( $phpIni );
            }

            $env = [
                '--env',
                self::XDEBUG_ENV,
            ];
        } else {
            $env = [
                '--env',
                'WP_CLI_PHP_ARGS',
            ];
        }

        $exec = [
            '/usr/local/bin/wp',
            '--allow-root',
        ];

        $containerId = $container->getId();

        $params = array_merge( $params, $env, [ $containerId ], $exec, $this->argument( 'args' ) );

        return Artisan::call( Docker::class, $params );
    }

}
