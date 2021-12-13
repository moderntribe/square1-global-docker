<?php declare(strict_types=1);

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\Docker;
use App\Commands\LocalDocker\Composer;
use App\Contracts\ArgumentRewriter;
use Illuminate\Support\Facades\Artisan;

final class ComposerTest extends LocalDockerCommand {

    public function test_it_runs_composer_command() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->container->shouldReceive( 'getId' )->once()->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--tty',
            'php-fpm-container-id',
            'composer',
            'install',
            '-d',
            '/application/www',
        ] );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Composer::class );

        $tester = $this->runCommand( $command, [
            'args' => [
                'install',
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

    public function test_it_runs_composer_command_with_proxy_version_option() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->container->shouldReceive( 'getId' )->once()->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--tty',
            'php-fpm-container-id',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
            '-d',
            '/application/www',
        ] );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Composer::class );

        $tester = $this->runCommand( $command, [
            'args' => [
                ArgumentRewriter::OPTION_VERSION
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

    public function test_it_runs_composer_command_with_proxy_version_flag() {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );

        $this->container->shouldReceive( 'getId' )->once()->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--tty',
            'php-fpm-container-id',
            'composer',
            ArgumentRewriter::FLAG_VERSION_PROXY,
            '-d',
            '/application/www',
        ] );

        Artisan::swap( $this->docker );

        $command = $this->app->make( Composer::class );

        $tester = $this->runCommand( $command, [
            'args' => [
                ArgumentRewriter::FLAG_VERSION
            ],
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

}
