<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Shell;
use Illuminate\Support\Facades\Artisan;

class ShellTest extends LocalDockerCommand {

    public function test_it_calls_local_shell_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getComposeFile' )->andReturn( $this->composeFile );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'exec',
            '--user',
            'squareone',
            'php-fpm',
            '/bin/bash',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Shell::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Launching shell for squareone...', $tester->getDisplay() );
    }

    public function test_it_shows_user_alternative_command_on_old_docker_images() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getComposeFile' )->andReturn( $this->composeFile );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'exec',
            '--user',
            'squareone',
            'php-fpm',
            '/bin/bash',
        ] )->andReturn( 1 );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Shell::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Whoops! This project is using an older php-fpm container. Try running "so shell --user root" instead', $tester->getDisplay() );
    }

}
