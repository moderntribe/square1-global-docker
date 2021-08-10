<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\BaseCommand;
use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Test;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class TestTest extends LocalDockerCommand {

    public function test_it_calls_local_test_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getPhpIni' )->andReturn( storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' ) );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            'php-tests',
            'php',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/dev/tests',
            'clean',
        ] );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            'php-tests',
            'php',
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
        Storage::disk( 'local' )->put( 'tests/dev/docker/php/php-ini-overrides.ini', 'xdebug.mode=debug,profile,trace' );

        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getPhpIni' )->andReturn( storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' ) );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            '--env',
            BaseCommand::XDEBUG_ENV,
            '-T',
            'php-fpm',
            'php',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/other/tests',
            'clean',
        ] );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            '--env',
            BaseCommand::XDEBUG_ENV,
            '-T',
            'php-fpm',
            'php',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/other/tests',
            'run',
            'integration',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Test::class );

        $tester = $this->runCommand( $command, [
            '--xdebug'    => true,
            '--container' => 'php-fpm',
            '--notty'     => true,
            '--path'      => '/application/www/other/tests',
            'args'        => [
                'run',
                'integration',
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringNotContainsString( 'not configured correctly for xdebug v3.0', $tester->getDisplay() );
    }

    public function test_it_warns_the_user_if_xdebug_is_not_correctly_configured() {
        Storage::disk( 'local' )->put( 'tests/dev/docker/php/php-ini-overrides.ini', 'xdebug.remote_enabled=1' );

        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getPhpIni' )->andReturn( storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' ) );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            '--env',
            BaseCommand::XDEBUG_ENV,
            '-T',
            'php-fpm',
            'php',
            '/application/www/vendor/bin/codecept',
            '-c',
            '/application/www/dev/tests',
            'clean',
        ] );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--env',
            'COMPOSE_INTERACTIVE_NO_CLI=1',
            '--env',
            'PHP_IDE_CONFIG=serverName=squareone.tribe',
            '--env',
            BaseCommand::XDEBUG_ENV,
            '-T',
            'php-fpm',
            'php',
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
        $this->assertStringContainsString( 'not configured correctly for xdebug v3.0', $tester->getDisplay() );
    }

}
