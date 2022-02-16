<?php declare( strict_types=1 );

namespace App\Commands;

use App\Contracts\ArgumentRewriter;
use App\Contracts\Runner;
use App\Factories\ParameterFactory;
use App\Recorders\ResultRecorder;
use App\Services\PermissionManager;
use App\Traits\ArgumentRewriterTrait;

/**
 * Docker Facade / Proxy Command
 *
 * @package App\Commands
 */
class Docker extends BaseCommand implements ArgumentRewriter {

    use ArgumentRewriterTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'docker ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Pass through for the docker binary';

    /**
     * Creates our Parameter Manager Object.
     *
     * @var \App\Factories\ParameterFactory
     */
    protected $factory;

    /**
     * @param  \App\Factories\ParameterFactory  $factory
     */
    public function __construct( ParameterFactory $factory ) {
        parent::__construct();

        $this->factory = $factory;

        // Allow this command to receive any options/arguments
        $this->ignoreValidationErrors();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner            $runner    The command runner.
     * @param  \App\Recorders\ResultRecorder    $recorder  The command result recorder.
     * @param  \App\Services\PermissionManager  $permission The permission manager.
     *
     * @return int
     */
    public function handle( Runner $runner, ResultRecorder $recorder, PermissionManager $permission ): int {
        $input = $this->factory->make( $this->input );

        // Ensure "docker exec" commands contain a --user option
        if ( $input->has( [ 'exec' ] ) && ! $input->has( [ '--user' ] ) ) {
            $input->add( [
                '--user',
                sprintf( '%d:%d', $permission->uid(), $permission->gid() ),
            ], 'exec' );
        }

        $tty = false;

        if ( $input->has( [ '-t', '--tty' ] ) ) {
            $tty = true;
        }

        // Force all docker exec commands to be interactive so the user can provide input, if required.
        if ( ! $input->has( [ '-i', '--interactive' ] ) ) {
            $input->add( [ '--interactive' ], 'exec' );
        }

        // Add back in --version, -V if required
        $this->restoreVersionArguments( $input );

        // Convert the entire command to a string
        $command = (string) $input;

        $response = $runner->output( $this )
                           ->tty( $tty )
                           ->run( $command );

        $recorder->add( $response->process()->getOutput() );

        return $response->ok() ? self::SUCCESS : self::FAILURE;
    }

}
