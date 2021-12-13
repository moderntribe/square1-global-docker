<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\BaseCommand;
use App\Commands\Docker;
use App\Commands\LocalDocker\Wp;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class WpTest extends LocalDockerCommand {

    private $workdir = '/application/www';

    public function test_it_calls_local_wp_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getWorkdir' )->andReturn( $this->workdir );

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--tty',
            '-w',
            '/application/www',
            '--env',
            'WP_CLI_PHP_ARGS',
            'php-fpm-container-id',
            '/usr/local/bin/wp',
            '--allow-root',
            'option',
            'get',
            'home',
        ] );

        Artisan::swap( $this->docker );

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
        Storage::disk( 'local' )->put( 'tests/dev/docker/php/php-ini-overrides.ini', 'xdebug.mode=debug,profile,trace' );

        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getWorkdir' )->andReturn( $this->workdir );
        $this->config->shouldReceive( 'getPhpIni' )->andReturn( storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' ) );

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '',
            '-w',
            '/application/www',
            '--env',
            BaseCommand::XDEBUG_ENV,
            'php-fpm-container-id',
            '/usr/local/bin/wp',
            '--allow-root',
            'option',
            'get',
            'home',
        ] );

        Artisan::swap( $this->docker );

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
        $this->assertStringNotContainsString( 'not configured correctly for xdebug v3.0', $tester->getDisplay() );
    }

    public function test_it_warns_the_user_if_xdebug_is_not_correctly_configured() {
        Storage::disk( 'local' )->put( 'tests/dev/docker/php/php-ini-overrides.ini', 'xdebug.remote_enabled=1' );

        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getWorkdir' )->andReturn( $this->workdir );
        $this->config->shouldReceive( 'getPhpIni' )->andReturn( storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' ) );

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '',
            '-w',
            '/application/www',
            '--env',
            BaseCommand::XDEBUG_ENV,
            'php-fpm-container-id',
            '/usr/local/bin/wp',
            '--allow-root',
            'option',
            'get',
            'home',
        ] );

        Artisan::swap( $this->docker );

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
        $this->assertStringContainsString( 'not configured correctly for xdebug v3.0', $tester->getDisplay() );
    }

}
