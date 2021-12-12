<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runner\Runners;

use App\Contracts\CustomCommandRunner;
use App\Contracts\Runner;
use App\Services\CustomCommands\CommandDefinition;
use App\Services\Docker\Container;
use Illuminate\Console\BufferedConsoleOutput;

/**
 * Run a command on the host computer.
 */
class HostRunner extends CustomCommandRunner {

    /**
     * @var \App\Contracts\Runner
     */
    protected $runner;

    public function __construct( Container $container, Runner $runner ) {
        parent::__construct( $container );

        $this->runner = $runner;
    }

    /**
     * Execute a single custom command on the host computer.
     *
     * @param  \App\Services\CustomCommands\CommandDefinition  $command
     * @param  array<string, mixed>                            $arguments
     * @param  array<string, mixed>                            $options
     *
     * @return void
     */
    protected function execute( CommandDefinition $command, array $arguments, array $options ): void {
        $parameters = [];

        foreach ( $arguments as $value ) {
            $parameters[] = $value;
        }

        foreach ( $options as $name => $value ) {
            $parameters[] = '--' . $name . '=' . $value;
        }

        $args = array_merge( [], explode( ' ', $command->cmd ), $parameters );
        $args = array_filter( $args );

        $this->runner
            ->output( new BufferedConsoleOutput() )
            ->run( $args )
            ->throw();
    }

}
