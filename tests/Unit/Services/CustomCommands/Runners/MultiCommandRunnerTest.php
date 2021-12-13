<?php declare( strict_types=1 );

namespace Tests\Unit\Services\CustomCommands\Runners;

use App\Services\CustomCommands\CommandDefinition;
use App\Services\CustomCommands\Runners\HostCommandRunner;
use App\Services\CustomCommands\Runners\MultiCommandRunner;
use App\Services\CustomCommands\Runners\ServiceCommandRunner;
use Closure;
use Illuminate\Contracts\Pipeline\Pipeline;
use Mockery;
use Tests\TestCase;

final class MultiCommandRunnerTest extends TestCase {

    public function test_it_executes_multiple_service_commands_in_separate_containers() {
        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->cmd       = [
            [
                'php-fpm'   => 'ls',
            ],
            [
                'php-tests' => 'ls -al',
            ],
        ];

        $serviceRunner = $this->mock( ServiceCommandRunner::class );

        $serviceRunner->shouldReceive( 'run' )
                            ->twice()
                            ->with( Mockery::on( function ( $command ) {
                                if ( $command->cmd == 'ls -al' && $command->service === 'php-tests' ) {
                                    return true;
                                }

                                if ( $command->cmd === 'ls' && $command->service === 'php-fpm' ) {
                                    return true;
                                }

                                return false;
                            } ), Mockery::type( Closure::class ) );

        /** @var Pipeline $pipeline */
        $pipeline = $this->app->make( Pipeline::class );

        $pipeline->via( 'run' )
                 ->send( $command )
                 ->through( [
                     MultiCommandRunner::class,
                     HostCommandRunner::class,
                     $serviceRunner,
                 ] )->thenReturn();
    }

    public function test_it_executes_multiple_host_commands_in_separate_containers() {
        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->cmd       = [
            'ls',
            'ls -al',
        ];

        $hostRunner = $this->mock( HostCommandRunner::class );

        $hostRunner->shouldReceive( 'run' )
                            ->twice()
                            ->with( Mockery::on( function ( $command ) {
                                if ( $command->cmd == 'ls -al' || $command->cmd === 'ls' ) {
                                    return true;
                                }

                                return false;
                            } ), Mockery::type( Closure::class ) );

        /** @var Pipeline $pipeline */
        $pipeline = $this->app->make( Pipeline::class );

        $pipeline->via( 'run' )
                 ->send( $command )
                 ->through( [
                     MultiCommandRunner::class,
                     $hostRunner,
                     ServiceCommandRunner::class,
                 ] )->thenReturn();
    }

    public function test_it_skips_multiple_command_running_for_single_commands() {
        $command            = new CommandDefinition();
        $command->signature = 'ls';
        $command->cmd       = 'ls';
        $command->service   = 'php-fpm';

        $serviceRunner = $this->mock( ServiceCommandRunner::class );

        $serviceRunner->shouldReceive( 'run' )
                      ->once()
                      ->with( Mockery::on( function ( $command ) {
                          if ( $command->cmd === 'ls' && $command->service === 'php-fpm' ) {
                              return true;
                          }

                          return false;
                      } ), Mockery::type( Closure::class ) );

        /** @var Pipeline $pipeline */
        $pipeline = $this->app->make( Pipeline::class );

        $pipeline->via( 'run' )
                 ->send( $command )
                 ->through( [
                     MultiCommandRunner::class,
                     HostCommandRunner::class,
                     $serviceRunner,
                 ] )->thenReturn();
    }

}
