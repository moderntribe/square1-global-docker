<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Contracts\Runner;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local automated tests command
 *
 * @package App\Commands\LocalDocker
 */
class Test extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'test {args*             : arguments passed to the wp binary}
                           {--x|xdebug              : Enable xdebug}
                           {--c|container=php-tests : Set the docker container to run the tests on}
                           {--noclean               : Do not run the codecept clean command first}
                           {--notty                 : Disable interactive/tty to capture output}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run codeception tests in the SquareOne local container';

    /**
     * The container to run tests in.
     *
     * @var string
     */
    protected $container;

    /**
     * Test constructor.
     *
     * @param  string  $container
     */
    public function __construct( string $container ) {
        parent::__construct();

        $this->container = $container;
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     *
     * @return void
     */
    public function handle( Config $config ): void {
        $params = [
            '--project-name',
            $config->getProjectName(),
            '--file',
            $config->getComposeFile(),
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            "PHP_IDE_CONFIG=serverName={$config->getProjectName()}.tribe",
        ];

        if ( $this->option( 'notty' ) ) {
            $params = array_merge( $params, [ '-T' ] );
        }

        $container = $this->option( 'container' ) ? $this->option( 'container' ) : $this->container;

        $params = array_merge( $params, [ $container ] );

        if ( $this->option( 'xdebug' ) ) {
            $exec = [
                'php',
                '-dxdebug.remote_autostart=1',
                '-dxdebug.remote_host=host.tribe',
                '-dxdebug.remote_enable=1',
                '/application/www/vendor/bin/codecept',
                '-c',
                "/application/www/dev/tests",
            ];
        } else {
            $exec = [
                'php',
                '-dxdebug.remote_autostart=0',
                '-dxdebug.remote_enable=0',
                '/application/www/vendor/bin/codecept',
                '-c',
                '/application/www/dev/tests',
            ];
        }

        // Clean codeception first.
        if ( ! $this->option( 'noclean' ) ) {
            $clean = array_merge( $params, $exec, [ 'clean' ] );
            Artisan::call( DockerCompose::class, $clean );
        }

        $params = array_merge( $params, $exec, $this->argument( 'args' ) );

        Artisan::call( DockerCompose::class, $params );
    }

}
