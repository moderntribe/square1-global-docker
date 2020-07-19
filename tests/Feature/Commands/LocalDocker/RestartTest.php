<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Restart;
use Illuminate\Support\Facades\Artisan;

class RestartTest extends LocalDockerCommand {

    public function test_it_calls_local_restart_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getComposeFile' )->andReturn( $this->composeFile );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'restart',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Restart::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Restarting project squareone...', $tester->getDisplay() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

}
