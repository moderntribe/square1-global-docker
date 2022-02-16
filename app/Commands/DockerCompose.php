<?php declare( strict_types=1 );

namespace App\Commands;

use App\Contracts\ArgumentRewriter;
use App\Contracts\Runner;
use App\Factories\ParameterFactory;
use App\Recorders\ResultRecorder;
use App\Services\Docker\Local\Config;
use App\Services\Docker\Network;
use App\Services\PermissionManager;
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
     * Creates our Parameter Manager Object.
     *
     * @var \App\Factories\ParameterFactory
     */
    protected $factory;

    /**
     * The docker compose binary to use.
     *
     * @var string
     */
    protected $binary;

    /**
     * DockerCompose constructor.
     *
     * @param  string                           $binary
     * @param  \App\Factories\ParameterFactory  $factory
     */
    public function __construct( ParameterFactory $factory, string $binary = 'docker-compose' ) {
        parent::__construct();

        $this->factory = $factory;
        $this->binary  = $binary;

        // Allow this command to receive any options/arguments
        $this->ignoreValidationErrors();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner            $runner    The command runner.
     * @param  \App\Services\Docker\Network     $network   The network manager.
     * @param  \App\Recorders\ResultRecorder    $recorder  The command result recorder.
     * @param  \App\Services\PermissionManager  $permission The permission manager.
     *
     * @return int
     */
    public function handle( Runner $runner, Network $network, ResultRecorder $recorder, PermissionManager $permission ): int {
        $input = $this->factory->make( $this->input );

        // Replace version options and flags
        $this->restoreVersionArguments( $input );

        $tty = true;

        if ( $input->has( [ '-T' ] ) ) {
            $tty = false;
        }

        // Determine if we use "docker-compose" or "docker compose" (v2)
        if ( $input->command() !== $this->binary ) {
            $input->replaceCommand( $this->binary );
        }

        // Convert the entire command to a string
        $command = (string) $input;

        $response = $runner->output( $this )
                           ->tty( $tty )
                           ->withEnvironmentVariables( [
                               Config::ENV_UID => $permission->uid(),
                               Config::ENV_GID => $permission->gid(),
                               'HOSTIP'        => $network->getGateWayIP(),
                           ] )
                           ->run( $command );

        $recorder->add( $response->process()->getOutput() );

        return $response->ok() ? self::SUCCESS : self::FAILURE;
    }

}
