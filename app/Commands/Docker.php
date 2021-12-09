<?php declare( strict_types=1 );

namespace App\Commands;

use App\Contracts\ArgumentRewriter;
use App\Contracts\Runner;
use App\Recorders\ResultRecorder;
use App\Services\Docker\Local\Config;
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

    public function __construct() {
        parent::__construct();

        // Allow this command to receive any options/arguments
        $this->ignoreValidationErrors();
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner          $runner    The command runner.
     * @param  \App\Recorders\ResultRecorder  $recorder  The command result recorder.
     *
     * @return int
     */
    public function handle( Runner $runner, ResultRecorder $recorder ): int {
        // Get the entire input passed to this command.
        $command = (string) $this->input;

        // Replace version options and flags
        $command = $this->restoreVersionArguments( $command );

        $tty = false;

        if ( str_contains( $command, '-t' ) || str_contains( $command, '--tty' ) ) {
            $tty = true;
        }

        $response = $runner->output( $this )
                           ->tty( $tty )
                           ->withEnvironmentVariables( [
                               Config::ENV_UID => Config::uid(),
                               Config::ENV_GID => Config::gid(),
                           ] )
                           ->run( $command );

        $recorder->add( $response->process()->getOutput() );

        if ( ! $response->ok() ) {
            return self::EXIT_ERROR;
        }

        return self::EXIT_SUCCESS;
    }

}
