<?php declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Commands\Docker;
use App\Contracts\ArgumentRewriter;
use App\Runners\CommandRunner;
use Symfony\Component\Process\Process;

final class DockerTest extends BaseCommandTester {

    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $process = $this->mock( Process::class );
        $process->shouldReceive( 'getOutput' )->andReturn( 'docker output would be here...' );

        $this->runner = $this->mock( CommandRunner::class );
        $this->runner->shouldReceive( 'output' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'withEnvironmentVariables' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'process' )->once()->andReturn( $process );
    }

    public function test_it_can_proxy_docker_commands() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker exec --tty php-fpm-container-id '/bin/bash'" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( Docker::class );

        $tester = $this->runCommand( $command, [
            'exec',
            '--tty',
            'php-fpm-container-id',
            '/bin/bash',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_can_proxy_version_options() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( sprintf(
                         "docker exec --tty php-fpm-container-id php %s",
                         ArgumentRewriter::OPTION_VERSION )
                     )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( Docker::class );

        $tester = $this->runCommand( $command, [
            'exec',
            '--tty',
            'php-fpm-container-id',
            'php',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_can_proxy_version_flags() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( sprintf(
                         "docker exec --tty php-fpm-container-id composer %s",
                         ArgumentRewriter::FLAG_VERSION )
                     )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( Docker::class );

        $tester = $this->runCommand( $command, [
            'exec',
            '--tty',
            'php-fpm-container-id',
            'composer',
            ArgumentRewriter::FLAG_VERSION_PROXY,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_can_disable_tty() {
        $this->runner->shouldReceive( 'tty' )->with( false )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker exec php-fpm-container-id '/bin/bash'" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( Docker::class );

        $tester = $this->runCommand( $command, [
            'exec',
            'php-fpm-container-id',
            '/bin/bash',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_returns_failed_exit_code_on_bad_command() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker exec --tty php-fpm-container-id '/bin/invalid-command'" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnFalse();

        $command = $this->app->make( Docker::class );

        $tester = $this->runCommand( $command, [
            'exec',
            '--tty',
            'php-fpm-container-id',
            '/bin/invalid-command',
        ] );

        $this->assertSame( 1, $tester->getStatusCode() );
    }

}
