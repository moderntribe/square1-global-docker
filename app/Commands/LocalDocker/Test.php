<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\Docker;
use App\Services\Docker\Container;
use App\Services\Docker\Local\Config;
use App\Services\XdebugValidator;
use App\Traits\XdebugWarningTrait;
use Illuminate\Support\Facades\Artisan;

/**
 * Local automated tests command
 *
 * @package App\Commands\LocalDocker
 */
class Test extends BaseLocalDocker {

    use XdebugWarningTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'test {args*                         : arguments passed to the wp binary}
                           {--x|xdebug                          : Enable xdebug}
                           {--c|container=php-tests             : Set the docker container to run the tests on}
                           {--p|path=/application/www/dev/tests : The path to the tests directory in the container}
                           {--noclean                           : Do not run the codecept clean command first}
                           {--notty                             : Disable interactive/tty to capture output}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run codeception tests in the SquareOne php container';

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
     * @param  \App\Services\XdebugValidator      $xdebugValidator
     * @param  \App\Services\Docker\Container     $container
     *
     * @return void
     */
    public function handle( Config $config, XdebugValidator $xdebugValidator, Container $container ): void {
        $params = [
            'exec',
            ! $this->option( 'notty' ) ? '--tty' : '',
        ];

        if ( $this->option( 'xdebug' ) ) {
            $phpIni = $config->getPhpIni();

            if ( ! $xdebugValidator->valid( $phpIni ) ) {
                $this->outdatedXdebugWarning( $phpIni );
            }

            $params = array_merge( $params, [
                '--env',
                "PHP_IDE_CONFIG=serverName={$config->getProjectName()}.tribe",
                '--env',
                self::XDEBUG_ENV,
            ] );
        }

        $containerName = $this->option( 'container' ) ? $this->option( 'container' ) : $this->container;
        $containerId   = $container->getId( $containerName );

        $params = array_merge( $params, [ $containerId ] );

        $exec = [
            'php',
            '/application/www/vendor/bin/codecept',
            '-c',
            $this->option( 'path' ),
        ];

        // Clean codeception first.
        if ( ! $this->option( 'noclean' ) ) {
            $clean = array_merge( $params, $exec, [ 'clean' ] );
            Artisan::call( Docker::class, $clean );
        }

        $params = array_merge( $params, $exec, $this->argument( 'args' ) );

        Artisan::call( Docker::class, $params );
    }

}
