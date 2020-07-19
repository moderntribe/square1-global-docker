<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Test;
use Illuminate\Support\Facades\Artisan;

class TestTest extends LocalDockerCommand {

    public function test_it_calls_local_test_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getComposeFile' )->andReturn( $this->composeFile );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            'php-tests',
            'php',
            '-dxdebug.remote_autostart=0',
            '-dxdebug.remote_enable=0',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/dev/tests',
            'clean',
        ] );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            'php-tests',
            'php',
            '-dxdebug.remote_autostart=0',
            '-dxdebug.remote_enable=0',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/dev/tests',
            'run',
            'integration',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Test::class );

        $tester = $this->runCommand( $command, [
            'args' => [
                'run',
                'integration',
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_calls_local_test_command_with_options() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getComposeFile' )->andReturn( $this->composeFile );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            '-T',
            'php-fpm',
            'php',
            '-dxdebug.remote_autostart=1',
            '-dxdebug.remote_host=host.tribe',
            '-dxdebug.remote_enable=1',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/dev/tests',
            'clean',
        ] );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            '--file',
            $this->composeFile,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            '-T',
            'php-fpm',
            'php',
            '-dxdebug.remote_autostart=1',
            '-dxdebug.remote_host=host.tribe',
            '-dxdebug.remote_enable=1',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/dev/tests',
            'run',
            'integration',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Test::class );

        $tester = $this->runCommand( $command, [
            '--xdebug'    => true,
            '--container' => 'php-fpm',
            '--notty'     => true,
            'args'        => [
                'run',
                'integration',
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

}
