<?php declare( strict_types=1 );

namespace App\Contracts;

use App\Services\CustomCommands\CommandDefinition;
use App\Services\Docker\Container;

/**
 * Custom Command Runner Strategy.
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
     * @param  array                                           $arguments
     * @param  array                                           $options
     *
     * @return void
     */
    public function run( CommandDefinition $command, array $arguments = [], array $options = [] ): void {
        $this->execArgs = array_merge( $this->execArgs, array_filter( [
            $command->interactive ? '--interactive' : '',
            $command->tty ? '--tty' : '',
            '--user',
            $command->user,
        ] ) );

        // Add environment variables
        if ( ! empty( $command->env ) ) {
            $env = [];

            foreach ( $command->env as $var => $value ) {
                // Support hyphen yaml syntax, e.g. - VAR: value
                if ( is_array( $value ) ) {
                    foreach ( $value as $subVar => $subValue ) {
                        $env[] = [
                            '--env',
                            "$subVar=$subValue",
                        ];
                    }
                } else {
                    $env[] = [
                        '--env',
                        "$var=$value",
                    ];
                }
            }

            $this->execArgs = array_merge( $this->execArgs, $env );
        }

        $this->execute( $command, $arguments, $options );
    }

    /**
     * Execute the custom command.
     *
     * @param  \App\Services\CustomCommands\CommandDefinition  $command
     * @param  array<string, mixed>                            $arguments
     * @param  array<string, mixed>                            $options
     *
     * @return void
     */
    abstract protected function execute( CommandDefinition $command, array $arguments, array $options ): void;

}
