<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Composer;
use Illuminate\Support\Facades\Artisan;

class ComposerTest extends LocalDockerCommand {

    public function test_it_runs_local_composer_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getComposeFile' )->andReturn( $this->composeFile );

        $this->dockerCompose = $this->mock( DockerCompose::class );
        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'exec',
            'php-fpm',
            'composer',
            'install',
            '-d',
            '/application/www',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Composer::class );

        $tester = $this->runCommand( $command, [
            'args' => [
                'install',
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }
}
