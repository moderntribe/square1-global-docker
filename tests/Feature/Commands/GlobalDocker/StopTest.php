<?php

namespace Tests\Feature\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use App\Commands\GlobalDocker\Stop;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Commands\BaseCommandTester;

class StopTest extends BaseCommandTester {

    private $dockerCompose;

    protected function setUp(): void {
        parent::setUp();

        $this->dockerCompose = $this->mock( DockerCompose::class );
    }

    public function test_it_runs_global_stop_command() {
        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            Stop::PROJECT_NAME,
            'down',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Stop::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Stopping global docker containers...', $tester->getDisplay() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

}
