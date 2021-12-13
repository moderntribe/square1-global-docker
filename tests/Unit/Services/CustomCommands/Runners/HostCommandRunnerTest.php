<?php declare( strict_types=1 );

namespace Tests\Unit\Services\CustomCommands\Runners;

use App\Runners\CommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use App\Services\CustomCommands\Runners\HostCommandRunner;
use Tests\TestCase;

final class HostCommandRunnerTest extends TestCase {

    /**
     * @var \App\Contracts\Runner
     */
    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
    }

    public function test_it_executes_a_host_command() {
        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->args      = [ '-al' ];
        $command->options   = [ 'color' => 'yes' ];
        $command->cmd       = 'ls';

        $this->runner->shouldReceive( 'throw' )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'output' )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( [
                         'ls',
                         '-al',
                         '--color=yes',
                     ] )->andReturnSelf();

        $closure = function() {};
        $hostRunner = $this->app->make( HostCommandRunner::class );
        $hostRunner->run( $command, $closure );
    }

    public function test_it_passes_command_on_if_not_a_host_command() {
        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->args      = [ '-al' ];
        $command->options   = [ 'color' => 'yes' ];
        $command->cmd       = 'ls';
        $command->service   = 'php-fpm';

        $this->runner->shouldNotReceive( 'run' );

        $closure = function() {};
        $hostRunner = $this->app->make( HostCommandRunner::class );
        $result = $hostRunner->run( $command, $closure );

        $this->assertNull( $result );
    }

}
