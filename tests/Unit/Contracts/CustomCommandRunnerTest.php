<?php declare(strict_types=1);

namespace Tests\Unit\Contracts;

use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use App\Services\Docker\Container;
use Closure;
use Tests\TestCase;

class CustomCommandRunnerTest extends TestCase {

    public function test_it_creates_custom_command_runner_pipeline_stage() {
        $container = $this->app->get( Container::class );

        $runner = new class( $container ) extends CustomCommandRunner {

            protected function execute( CommandDefinition $command, Closure $next ) {
                return null;
            }

        };

        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->cmd       = 'ls';

        $closure = function () {};

        $result = $runner->run( $command, $closure );

        $this->assertNull( $result );
    }

}
