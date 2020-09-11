<?php declare( strict_types=1 );

namespace App\Commands;

use App\Contracts\Runner;
use App\Services\Docker\Network;
use App\Recorders\ResultRecorder;
use App\Services\Docker\Local\Config;

/**
 * Docker Compose Facade / Proxy Command
 *
 * @package App\Commands
 */
class DockerCompose extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'docker-compose ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Pass through for docker-compose binary';

    /**
     * DockerCompose constructor.
     */
    public function __construct() {
        parent::__construct();

        // Allow this command to receive any options/arguments
        $this->ignoreValidationErrors();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner          $runner    The command runner.
     * @param  \App\Services\Docker\Network   $network   The network manager.
     * @param  \App\Recorders\ResultRecorder  $recorder  The command result recorder.
     *
     * @return int
     */
    public function handle( Runner $runner, Network $network, ResultRecorder $recorder ) {
        // Get the entire input passed to this command.
        $command = (string) $this->input;

        $tty = true;

        if ( str_contains( $command, '-T' ) ) {
            $tty = false;
        }

        $response = $runner->output( $this )
                           ->tty( $tty )
                           ->withEnvironmentVariables( [
                               Config::ENV_UID => Config::uid(),
                               Config::ENV_GID => Config::gid(),
                               'HOSTIP'        => $network->getGateWayIP(),
                           ] )
                           ->run( $command );

        $recorder->add( $response->process()->getOutput() );

        if ( ! $response->ok() ) {
            return self::EXIT_ERROR;
        }

        return self::EXIT_SUCCESS;
    }

}
