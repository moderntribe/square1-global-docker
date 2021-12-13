<?php declare(strict_types=1);

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\Docker;
use App\Commands\LocalDocker\Shell;
use Illuminate\Support\Facades\Artisan;

final class ShellTest extends LocalDockerCommand {

    public function test_it_calls_local_shell_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->container->shouldReceive( 'getId' )->once()->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--user',
            'squareone',
            'php-fpm-container-id',
            '/bin/bash',
        ] );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Shell::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Launching shell for squareone...', $tester->getDisplay() );
    }

    public function test_it_shows_user_alternative_command_on_old_docker_images() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->container->shouldReceive( 'getId' )->once()->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--user',
            'squareone',
            'php-fpm-container-id',
            '/bin/bash',
        ] )->andReturn( 1 );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Shell::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Whoops! This project is using an older php-fpm container. Try running "so shell --user root" instead', $tester->getDisplay() );
    }

}
