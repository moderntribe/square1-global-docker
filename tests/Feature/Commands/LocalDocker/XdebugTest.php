<?php declare(strict_types=1);

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\Docker;
use App\Commands\LocalDocker\Xdebug;
use Illuminate\Support\Facades\Artisan;

final class XdebugTest extends LocalDockerCommand {

    public function test_it_shows_xdebug_is_on() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--tty',
            '--user',
            'root',
            'php-fpm-container-id',
            'bash',
            '-c',
            '[[ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]]',
        ] )->andReturn( 0 );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'xdebug is on', $tester->getDisplay() );
    }

    public function test_it_shows_xdebug_is_off() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--tty',
            '--user',
            'root',
            'php-fpm-container-id',
            'bash',
            '-c',
            '[[ -f /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ]]',
        ] )->andReturn( 1 );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'xdebug is off', $tester->getDisplay() );
    }

    public function test_it_would_enable_xdebug() {
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--user',
            'root',
            'php-fpm-container-id',
            'mv',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disabled',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
        ] );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--user',
            'root',
            'php-fpm-container-id',
            'kill',
            '-USR2',
            '1',
        ] );

        Artisan::swap( $this->docker );

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

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--user',
            'root',
            'php-fpm-container-id',
            'mv',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini',
            '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini.disabled',
        ] );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--user',
            'root',
            'php-fpm-container-id',
            'kill',
            '-USR2',
            '1',
        ] );

        Artisan::swap( $this->docker );

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

        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Xdebug::class );

        $tester = $this->runCommand( $command, [
            'action' => 'badarg',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Invalid argument: badarg. Allowed values: on|off', $tester->getDisplay() );
    }

}
