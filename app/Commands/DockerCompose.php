<?php declare( strict_types=1 );

namespace App\Commands;

use App\Contracts\ArgumentRewriter;
use App\Contracts\Runner;
use App\Recorders\ResultRecorder;
use App\Services\Docker\Local\Config;
use App\Services\Docker\Network;
use App\Traits\ArgumentRewriterTrait;

/**
 * Docker Compose Facade / Proxy Command
 *
 * @package App\Commands
 */
class DockerCompose extends BaseCommand implements ArgumentRewriter {

    use ArgumentRewriterTrait;

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
     * The docker compose binary to use.
     *
     * @var string
     */
    protected $binary;

    /**
     * DockerCompose constructor.
     *
     * @param  string  $binary
     */
    public function __construct( string $binary = 'docker-compose' ) {
        parent::__construct();

        $this->binary = $binary;

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
    public function handle( Runner $runner, Network $network, ResultRecorder $recorder ): int {
        // Get the entire input passed to this command.
        $command = (string) $this->input;

        // Replace version options and flags
        $command = $this->restoreVersionArguments( $command );

        if ( ! str_contains( $this->binary, $command ) ) {
            $command = str_replace( 'docker-compose', $this->binary, $command );
        }

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
