<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\BaseCommand;
use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Wp;
use Illuminate\Support\Facades\Artisan;

class WpTest extends LocalDockerCommand {

    public function test_it_calls_local_wp_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--env',
            'WP_CLI_PHP_ARGS',
            'php-fpm',
            '/usr/local/bin/wp',
            '--allow-root',
            'option',
            'get',
            'home',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Wp::class );

        $tester = $this->runCommand( $command, [
            'args' => [
                'option',
                'get',
                'home',
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_calls_local_wp_command_with_options() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '-T',
            '--env',
            BaseCommand::XDEBUG_ENV,
            'php-fpm',
            '/usr/local/bin/wp',
            '--allow-root',
            'option',
            'get',
            'home',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Wp::class );

        $tester = $this->runCommand( $command, [
            '--notty'  => true,
            '--xdebug' => true,
            'args'     => [
                'option',
                'get',
                'home',
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

}
