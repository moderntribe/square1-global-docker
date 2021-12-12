<?php declare(strict_types=1);

namespace App\Services\CustomCommands;

use App\Services\CustomCommands\Runners\RunnerCollection;
use Illuminate\Console\Application;
use Illuminate\Console\Parser;
use Illuminate\Contracts\Pipeline\Pipeline;

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
     * The Laravel Pipeline.
     *
     * @var \Illuminate\Contracts\Pipeline\Pipeline
     */
    protected $pipeline;

    /**
     * The Pipeline stages to run when executing a custom command.
     *
     * @var \App\Services\CustomCommands\Runners\RunnerCollection
     */
    protected $pipes;

    public function __construct(
        CommandCollection $commands,
        Pipeline $pipeline,
        RunnerCollection $pipes
    ) {
        $this->commands = $commands;
        $this->pipeline = $pipeline;
        $this->pipes    = $pipes;
    }

    /**
     * Register custom commands in the Laravel Kernel.
     *
     * @return void
     */
    public function register(): void {
        $commands = $this->commands;
        $pipeline = $this->pipeline->via( 'run' )->through( $this->pipes->toArray() );

        // Hook into the starting event and register the custom commands
        Application::starting( function ( $artisan ) use ( $commands, $pipeline ) {
            foreach ( $commands as $command ) {
                // Parse the command signature
                [ $name, $arguments, $options ] = Parser::parse( $command->signature );

                $c = new ClosureCommand(
                    $command->signature,
                    function ( ...$parameters )
                    use ( $command, $arguments, $options, $pipeline ) {
                        // The command is always the first item, remove it.
                        array_shift( $parameters );

                        $argCount = count( $arguments );

                        $commandArgs    = [];
                        $commandOptions = [];

                        // Build command arguments
                        foreach ( $arguments as $i => $arg ) {
                            $commandArgs[ $arg->getName() ] = $parameters[ $i ];
                        }

                        // Build command options
                        foreach ( $options as $i => $option ) {
                            $commandOptions[ $option->getName() ] = $parameters[ $i + $argCount ];
                        }

                        $command->args    = $commandArgs;
                        $command->options = $commandOptions;

                        // Run through the pipeline which will run the command with
                        // the appropriate runner.
                        $pipeline->send( $command )
                                 ->thenReturn();
                    } );

                $c->purpose( $command->description );

                $artisan->add( $c );
            }
        } );
    }

}
