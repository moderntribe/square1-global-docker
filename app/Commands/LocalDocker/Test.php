<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\XdebugValidator;
use App\Traits\XdebugWarningTrait;
use App\Services\Docker\Local\Config;
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
     * @param  \App\Services\XdebugValidator      $xdebugValidator
     *
     * @return void
     */
    public function handle( Config $config, XdebugValidator $xdebugValidator ): void {
        $params = [
            '--project-name',
            $config->getProjectName(),
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
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

        if ( $this->option( 'notty' ) ) {
            $params = array_merge( $params, [ '-T' ] );
        }

        $container = $this->option( 'container' ) ? $this->option( 'container' ) : $this->container;

        $params = array_merge( $params, [ $container ] );

        $exec = [
            'php',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/dev/tests',
        ];

        chdir( $config->getDockerDir() );

        // Clean codeception first.
        if ( ! $this->option( 'noclean' ) ) {
            $clean = array_merge( $params, $exec, [ 'clean' ] );
            Artisan::call( DockerCompose::class, $clean );
        }

        $params = array_merge( $params, $exec, $this->argument( 'args' ) );

        Artisan::call( DockerCompose::class, $params );
    }

}
