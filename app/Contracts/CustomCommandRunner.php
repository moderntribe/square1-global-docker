<?php declare(strict_types=1);

namespace App\Contracts;

use App\Services\CustomCommands\CommandDefinition;
use App\Services\Docker\Container;
use Closure;

/**
 * Custom Command Runner run as a Pipeline stage.
 */
abstract class CustomCommandRunner {

    /**
     * The default "docker" arguments.
     *
     * @var string[]
     */
    protected $execArgs = [
        'exec',
    ];

    /**
     * @var \App\Services\Docker\Container
     */
    protected $container;

    public function __construct( Container $container ) {
        $this->container = $container;
    }

    /**
     * Configure a command before execution.
     *
     * @param  \App\Services\CustomCommands\CommandDefinition  $command
     * @param  \Closure                                        $next
     */
    public function run( CommandDefinition $command, Closure $next ) {
        $this->execArgs = array_merge( $this->execArgs, array_filter( [
            $command->interactive ? '--interactive' : '',
            $command->tty ? '--tty' : '',
            '--user',
            $command->user,
        ] ) );

        // Add environment variables to pass to the container
        if ( ! empty( $command->env ) ) {
            foreach ( $command->env as $var => $value ) {

                // Support hyphen yaml syntax, e.g. - VAR: value
                if ( is_array( $value ) ) {
                    foreach ( $value as $subVar => $subValue ) {
                        $this->execArgs[] = '--env';
                        $this->execArgs[] = "$subVar=$subValue";
                    }
                } else {
                    $this->execArgs[] = '--env';
                    $this->execArgs[] = "$var=$value";
                }
            }
        }

        return $this->execute( $command, $next );
    }

    /**
     * Execute the pipe in the pipeline.
     *
     * @param  \App\Services\CustomCommands\CommandDefinition  $command
     * @param  \Closure                                        $next
     */
    abstract protected function execute( CommandDefinition $command, Closure $next );

}
