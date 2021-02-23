<?php declare( strict_types=1 );

namespace App\Commands\App;

use NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand as ZeroTestCommand;

/**
 * Override Laravel Zero's built in Test Command to change the signature so we can use the "test" command.
 *
 * @package App\Commands\App
 */
class TestCommand extends ZeroTestCommand {

    /**
     * Run this application's automated tests
     *
     * @var string
     */
    protected $signature = 'app:test
        {--without-tty : Disable output to TTY}
        {--parallel : Indicates if the tests should run in parallel}
        {--recreate-databases : Indicates if the test databases should be re-created}
    ';

}
