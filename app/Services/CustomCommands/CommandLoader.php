<?php declare(strict_types=1);

namespace App\Services\CustomCommands;

use App\Services\CustomCommands\Runner\RunnerFactory;
use Illuminate\Console\Application;
use Illuminate\Console\Parser;

/**
 * Load custom commands.
 */
class CommandLoader {

    /**
     * A collection of custom commands.
     *
     * @var \App\Services\CustomCommands\CommandCollection
     */
    protected $commands;

    /**
     * @var \App\Services\CustomCommands\Runner\RunnerFactory
     */
    protected $runnerFactory;

    public function __construct( CommandCollection $commands, RunnerFactory $runnerFactory ) {
        $this->commands      = $commands;
        $this->runnerFactory = $runnerFactory;
    }

    /**
     * Register custom commands in the Laravel Kernel.
     *
     * @return void
     */
    public function register(): void {
        $commands = $this->commands;
        $factory  = $this->runnerFactory;

        Application::starting( function ( $artisan ) use ( $commands, $factory ) {
            foreach ( $commands as $command ) {
                // Parse the command signature
                [ $name, $arguments, $options ] = Parser::parse( $command->signature );

                // Select the custom command runner
                $runner = $factory->make( $command );

                $c = new ClosureCommand( $command->signature, function ( ... $parameters ) use ( $command, $arguments, $options, $runner ) {
                    // The command is always the first item, remove it.
                    array_shift( $parameters );

                    $argCount = count( $arguments );

                    $commandArgs    = [];
                    $commandOptions = [];

                    foreach ( $arguments as $i => $arg ) {
                        $commandArgs[ $arg->getName() ] = $parameters[ $i ];
                    }

                    foreach ( $options as $i => $option ) {
                        $commandOptions[ $option->getName() ] = $parameters[ $i + $argCount ];
                    }

                    // Run the command with the appropriate strategy
                    $runner->run( $command, $commandArgs, $commandOptions );
                } );

                $c->purpose( $command->description );

                $artisan->add( $c );
            }
        } );
    }

}
