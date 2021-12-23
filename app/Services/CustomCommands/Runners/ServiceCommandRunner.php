<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runners;

use App\Commands\Docker;
use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use Closure;
use Illuminate\Support\Facades\Artisan;

/**
 * Run a command in a service container.
 */
class ServiceCommandRunner extends CustomCommandRunner {

    protected function execute( CommandDefinition $command, Closure $next ) {
        if ( empty( $command->service ) ) {
            return $next( $command );
        }

        $args = $this->execArgs;

        $container = $this->container->getId( $command->service );
        $args[]    = $container;

        $parameters = $command->args;

        foreach ( $command->options as $name => $value ) {
            $parameters[] = sprintf( '--%s=%s', $name, $value );
        }

        $args = array_merge( $args, explode( ' ', $command->cmd ), $parameters );
        $args = array_filter( $args );

        Artisan::call( Docker::class, $args );

        return $next( $command );
    }
}
