<?php declare( strict_types=1 );

namespace Tests\Unit\Services\CustomCommands\Runners;

use App\Commands\Docker;
use App\Commands\LocalDocker\BaseLocalDocker;
use App\Services\CustomCommands\CommandDefinition;
use App\Services\CustomCommands\Runners\ServiceCommandRunner;
use App\Services\Docker\Container;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

final class ServiceCommandRunnerTest extends TestCase {

    /**
     * @var \App\Services\Docker\Container
     */
    private $container;

    /**
     * @var \App\Commands\Docker
     */
    private $docker;

    protected function setUp(): void {
        parent::setUp();

        $this->container = $this->mock( Container::class );
        $this->docker    = $this->mock( Docker::class );
    }

    public function test_it_executes_a_service_command() {
        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->args      = [ '-al' ];
        $command->options   = [ 'color' => 'yes' ];
        $command->cmd       = 'ls';
        $command->service   = 'php-fpm';

        $this->container->shouldReceive( 'getId' )
            ->once()
            ->with( 'php-fpm' )
            ->andReturn( 'php-fpm-container-id' );

        Artisan::swap( $this->docker );

        $this->docker->shouldReceive( 'call' )
                     ->with( Docker::class, [
                         'exec',
                         '--interactive',
                         '--tty',
                         '--user',
                         'squareone',
                         'php-fpm-container-id',
                         'ls',
                         '-al',
                         '--color=yes',
                     ] )
                     ->once()
                     ->andReturn( BaseLocalDocker::EXIT_SUCCESS );

        $closure        = function() {};
        $serviceCommand = $this->app->make( ServiceCommandRunner::class );
        $result = $serviceCommand->run( $command, $closure );

        $this->assertNull( $result );
    }

    public function test_it_passes_command_on_if_not_a_service_command() {
        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->args      = [ '-al' ];
        $command->options   = [ 'color' => 'yes' ];
        $command->cmd       = 'ls';

        $this->docker->shouldNotReceive( 'call' );

        $closure        = function() {};
        $serviceCommand = $this->app->make( ServiceCommandRunner::class );
        $result = $serviceCommand->run( $command, $closure );

        $this->assertNull( $result );
    }

}
