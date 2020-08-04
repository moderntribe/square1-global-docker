<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Stop;
use Illuminate\Support\Facades\Artisan;

class StopTest extends LocalDockerCommand {

    public function test_it_calls_local_stop_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'down',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Stop::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Stopping project squareone...', $tester->getDisplay() );
    }

}
