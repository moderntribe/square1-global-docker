<?php

namespace Tests\Feature\Commands\GlobalDocker;

use App\Runners\CommandRunner;
use App\Commands\GlobalDocker\Status;
use Tests\Feature\Commands\BaseCommandTest;

class StatusTest extends BaseCommandTest {

    public function setUp(): void {
        parent::setUp();

        $runner = $this->mock( CommandRunner::class );
        $runner->shouldReceive( 'output' )->once()->andReturnSelf();
        $runner->shouldReceive( 'run' )->with( 'docker ps' )->once()->andReturnSelf();
        $runner->shouldReceive( 'throw' )->once()->andReturnSelf();
    }

    public function test_it_runs_global_status_command() {
        $command = $this->app->make( Status::class );
        $tester = $this->runCommand( $command, []);

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

}
