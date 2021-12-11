<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runner\Runners;

use App\Commands\Docker;
use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use App\Services\Docker\Container;
use Illuminate\Support\Facades\Artisan;

/**
 * Run a series of commands.
 */
class MultiRunner extends CustomCommandRunner {

    /**
     * Run commands on the host computer.
     *
     * @var \App\Services\CustomCommands\Runner\Runners\HostRunner
     */
    protected $hostRunner;

    public function __construct( Container $container, HostRunner $hostRunner ) {
        parent::__construct( $container );

        $this->hostRunner = $hostRunner;
    }

    /**
     * Execute a series commands, optionally in a specific service container.
     *
     * @param  \App\Services\CustomCommands\CommandDefinition  $command
     * @param  array<string, mixed>                            $arguments
     * @param  array<string, mixed>                            $options
     *
     * @return void
     */
    protected function execute( CommandDefinition $command, array $arguments = [], array $options = [] ): void {
        // Not sure why the Yaml parser brings these in backwards
        $subCommands = array_reverse( $command->cmd );

        foreach ( $subCommands as $subCommand ) {
            // Run command in a specific service container defined as - container: command
            if ( is_array( $subCommand ) ) {
                foreach ( $subCommand as $service => $commandValue ) {
                    $dockerArgs     = $this->execArgs;
                    $dockerArgs[]   = $this->container->getId( $service );
                    $subCommandArgs = array_merge( $dockerArgs, explode( ' ', $commandValue ) );

                    Artisan::call( Docker::class, $subCommandArgs );
                }
            } else {
                // Run the sub command on the host computer
                if ( empty( $command->service ) ) {
                    $hostCommand      = $command;
                    $hostCommand->cmd = $subCommand;
                    $this->hostRunner->run( $command, $arguments, $options );
                } else {
                    // Run the sub command in the service container provided
                    $dockerArgs   = $this->execArgs;
                    $dockerArgs[] = $this->container->getId( $command->service );

                    $subCommandArgs = array_merge( $dockerArgs, explode( ' ', $subCommand ) );

                    Artisan::call( Docker::class, $subCommandArgs );
                }
            }
        }
    }

}
