<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runners;

use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use App\Services\Docker\Container;
use Closure;
use Illuminate\Console\BufferedConsoleOutput;
use App\Contracts\Runner as RunnerContract;

/**
 * Run a custom command on the host computer.
 */
class HostCommandRunner extends CustomCommandRunner {

    /**
     * @var \App\Contracts\Runner
     */
    protected $runner;

    public function __construct( Container $container, RunnerContract $runner ) {
        parent::__construct( $container );

        $this->runner = $runner;
    }

    public function execute( CommandDefinition $command, Closure $next ) {
        if ( ! empty( $command->service ) ) {
            return $next( $command );
        }

        $parameters = $command->args;

        foreach ( $command->options as $name => $value ) {
            $parameters[] = '--' . $name . '=' . $value;
        }

        $args = array_merge( [], explode( ' ', $command->cmd ), $parameters );
        $args = array_filter( $args );

        $this->runner
            ->output( new BufferedConsoleOutput() )
            ->run( $args )
            ->throw();

        return $next( $command );
    }
}
