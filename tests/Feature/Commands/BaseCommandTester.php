<?php

namespace Tests\Feature\Commands;

use Illuminate\Console\Application;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestCase;

/**
 * Include a command running method to test commands.
 *
 * @package Tests\Feature\Commands
 */
abstract class BaseCommandTester extends TestCase {

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * The `CommandTester` is directly returned, use methods like
     * `->getDisplay()` or `->getStatusCode()` on it.
     *
     * @param  Command|\Symfony\Component\Console\Command\Command  $command
     * @param  array                                               $arguments         The command line arguments, array of key=>value
     *                                                                                Examples:
     *                                                                                - named  arguments: ['model' => 'Post']
     *                                                                                - boolean flags: ['--all' => true]
     *                                                                                - arguments with values: ['--arg' => 'value']
     * @param  array                                               $interactiveInput  Interactive responses to the command
     *                                                                                I.e. anything the command `->ask()` or `->confirm()`, etc.
     *
     * @return CommandTester
     */
    protected function runCommand( $command, array $arguments = [], array $interactiveInput = [] ): CommandTester {
        if ( method_exists( $command, 'setLaravel' ) ) {
            $command->setApplication( new ConsoleApplication() );
            $command->setLaravel( $this->app );
        } else {
            // This is a symfony command
            $command->setApplication( $this->app->make( Application::class, [ 'version' => '1.0.0' ] ) );
        }

        $tester = new CommandTester( $command );
        $tester->setInputs( $interactiveInput );

        $tester->execute( $arguments, [
            'interactive' => true,
        ] );

        return $tester;
    }

}
