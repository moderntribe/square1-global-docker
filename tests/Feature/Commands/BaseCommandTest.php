<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Include a command running method to test commands.
 *
 * @package Tests\Feature\Commands
 */
abstract class BaseCommandTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
    }

    /**
     * The `CommandTester` is directly returned, use methods like
     * `->getDisplay()` or `->getStatusCode()` on it.
     *
     * @param  Command  $command
     * @param  array    $arguments         The command line arguments, array of key=>value
     *                                     Examples:
     *                                     - named  arguments: ['model' => 'Post']
     *                                     - boolean flags: ['--all' => true]
     *                                     - arguments with values: ['--arg' => 'value']
     * @param  array    $interactiveInput  Interactive responses to the command
     *                                     I.e. anything the command `->ask()` or `->confirm()`, etc.
     *
     * @return CommandTester
     */
    protected function runCommand( Command $command, array $arguments = [], array $interactiveInput = [] ): CommandTester {
        $command->setApplication( new ConsoleApplication() );
        $command->setLaravel( $this->app );

        $tester = new CommandTester( $command );
        $tester->setInputs( $interactiveInput );

        $tester->execute( $arguments );

        return $tester;
    }

}
