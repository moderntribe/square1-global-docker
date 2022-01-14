<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runners;

use App\Contracts\CustomCommandRunner;
use App\Contracts\Runner as RunnerContract;
use App\Services\CustomCommands\CommandDefinition;
use App\Services\Docker\Container;
use Closure;
use Illuminate\Console\BufferedConsoleOutput;
use Illuminate\Support\Arr;

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

    protected function execute( CommandDefinition $command, Closure $next ) {
        if ( ! empty( $command->service ) ) {
            return $next( $command );
        }

        $parameters = $command->args;

        // Convert any of Laravel's input arrays into raw arguments/options
        $parameters = Arr::flatten( $parameters );

        // Add any Laravel command options that were passed
        foreach ( $command->options as $name => $value ) {
            $parameters[] = sprintf( '--%s=%s', $name, $value );
        }

        $args = array_merge( [], explode( ' ', $command->cmd ), $parameters );
        $args = array_filter( $args );

        // Convert back to string, so it calls Process::fromShellCommandline()
        $cmd = implode( ' ', $args );

        $this->runner
            ->output( new BufferedConsoleOutput() )
            ->run( $cmd )
            ->throw();

        return $next( $command );
    }

}
