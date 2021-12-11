<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runner\Runners;

use App\Commands\Docker;
use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use Illuminate\Support\Facades\Artisan;

/**
 * Run a command in a service container.
 */
class ServiceRunner extends CustomCommandRunner {

    /**
     * Execute a single custom command in a container.
     *
     * @param  \App\Services\CustomCommands\CommandDefinition  $command
     * @param  array<string, mixed>                            $arguments
     * @param  array<string, mixed>                            $options
     *
     * @return void
     */
    protected function execute( CommandDefinition $command, array $arguments, array $options ): void {
        $args = $this->execArgs;

        $container = $this->container->getId( $command->service );
        $args[]    = $container;

        $parameters = [];

        foreach ( $arguments as $value ) {
            $parameters[] = $value;
        }

        foreach ( $options as $name => $value ) {
            $parameters[] = '--' . $name . '=' . $value;
        }

        $args = array_merge( $args, explode( ' ', $command->cmd ), $parameters );
        $args = array_filter( $args );

        Artisan::call( Docker::class, $args );
    }

}
