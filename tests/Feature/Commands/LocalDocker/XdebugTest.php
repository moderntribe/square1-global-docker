<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Commands\LocalDocker\Xdebug;
use Illuminate\Support\Facades\Artisan;

class XdebugTest extends LocalDockerCommand {

    public function test_it_shows_xdebug_is_on() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--user',
            'root',
            'php-fpm',
            'bash',
            '-c',
            '[[ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]]',
        ] )->andReturn( 0 );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'xdebug is on', $tester->getDisplay() );
    }

    public function test_it_shows_xdebug_is_off() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--user',
            'root',
            'php-fpm',
            'bash',
            '-c',
            '[[ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]]',
        ] )->andReturn( 1 );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'xdebug is off', $tester->getDisplay() );
    }

    public function test_it_would_enable_xdebug() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '-T',
            '--user',
            'root',
            'php-fpm',
            'mv',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disabled',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
        ] );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--user',
            'root',
            'php-fpm',
            'kill',
            '-USR2',
            '1',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command, [
            'action' => 'on',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'xdebug enabled', $tester->getDisplay() );
    }

    public function test_it_would_disable_xdebug() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '-T',
            '--user',
            'root',
            'php-fpm',
            'mv',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disabled',
        ] );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            $this->project,
            'exec',
            '--user',
            'root',
            'php-fpm',
            'kill',
            '-USR2',
            '1',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command, [
            'action' => 'off',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'xdebug disabled', $tester->getDisplay() );
    }

    public function test_it_displays_error_on_invalid_argument() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command, [
            'action' => 'badarg',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Invalid argument: badarg. Allowed values: on|off', $tester->getDisplay() );
    }

}
