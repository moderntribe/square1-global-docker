<?php

namespace Tests\Feature\Commands\GlobalDocker;

use App\Commands\GlobalDocker\StopAll;
use App\Runners\CommandRunner;
use Tests\Feature\Commands\BaseCommandTest;

class StopAllTest extends BaseCommandTest {

    protected function setUp(): void {
        parent::setUp();

        $this->mock( CommandRunner::class, function ( $mock ) {
            $mock->shouldReceive( 'output' )->once()->andReturn( $mock );
            $mock->shouldReceive( 'run' )->with( 'docker stop $(docker ps -aq)' )->once()->andReturn( $mock );
            $mock->shouldReceive( 'throw' )->once()->andReturn( $mock );
        } );

    }

    public function test_it_runs_global_stop_all_command() {
        $command = $this->app->make( StopAll::class );
        $tester = $this->runCommand( $command, []);

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Stopping all docker containers...', $tester->getDisplay() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

}
