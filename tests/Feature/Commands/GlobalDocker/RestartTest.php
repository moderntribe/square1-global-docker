<?php

namespace Tests\Feature\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use App\Commands\GlobalDocker\Restart;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Commands\BaseCommandTester;

class RestartTest extends BaseCommandTester {

    private $dockerCompose;

    protected function setUp(): void {
        parent::setUp();

        $this->dockerCompose = $this->mock( DockerCompose::class );
    }

    public function test_it_runs_global_restart_command() {
        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            Restart::PROJECT_NAME,
            '--file',
            config( 'squareone.docker.compose' ),
            'restart',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Restart::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Restarting global docker containers...', $tester->getDisplay() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

}
