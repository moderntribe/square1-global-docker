<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Logs;
use Illuminate\Support\Facades\Artisan;

class LogsTest extends LocalDockerCommand {

    public function test_it_calls_local_logs_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'logs',
            '-f'
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Logs::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Displaying logs for squareone. Press command/ctrl + c to quit', $tester->getDisplay() );
    }

}
