<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runners;

use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use Closure;

/**
 * Pass commands to either the host or the docker service.
 */
class MultiCommandRunner extends CustomCommandRunner {

    public function execute( CommandDefinition $command, Closure $next ) {
        if ( ! is_array( $command->cmd ) ) {
            return $next( $command );
        }

        foreach ( $command->cmd as $subCommand ) {
            // Run command in a specific service container defined as
            // - container: command
            if ( is_array( $subCommand ) ) {
                foreach ( $subCommand as $service => $commandValue ) {
                    $c          = new CommandDefinition();
                    $c->service = $service;
                    $c->cmd     = $commandValue;

                    $next( $c );
                }
            } else {
                // Copy the sub command string and pass to the next pipeline stage
                $c      = $command;
                $c->cmd = $subCommand;

                $next( $c );
            }
        }

        return null;
    }
}
