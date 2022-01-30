<?php declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Commands\Docker;
use App\Contracts\ArgumentRewriter;
use App\Runners\CommandRunner;
use Symfony\Component\Process\Process;

final class DockerTest extends BaseCommandTester {

    private $runner;
    private $uid;
    private $gid;

    protected function setUp(): void {
        parent::setUp();

        $this->uid = getmyuid();
        $this->gid = getmygid();

        $process = $this->mock( Process::class );
        $process->shouldReceive( 'getOutput' )->andReturn( 'docker output would be here...' );

        $this->runner = $this->mock( CommandRunner::class );
        $this->runner->shouldReceive( 'output' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'process' )->once()->andReturn( $process );
    }

    public function test_it_can_proxy_docker_commands() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker exec --interactive --user '$this->uid:$this->gid' --tty php-fpm-container-id '/bin/bash'" )
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
                         "docker exec --interactive --user '%d:%d' --tty php-fpm-container-id php %s",
                                $this->uid,
                         $this->gid,
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
                         "docker exec --interactive --user '%d:%d' --tty php-fpm-container-id composer %s",
                         $this->uid,
                         $this->gid,
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
                     ->with( sprintf(
                         "docker exec --interactive --user '%d:%d' php-fpm-container-id '/bin/bash'",
                         $this->uid,
                         $this->gid,
                     ) )
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
                     ->with( sprintf( "docker exec --interactive --user '%d:%d' --tty php-fpm-container-id '/bin/invalid-command'",
                        $this->uid,
                     $this->gid,
                     ) )
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

    public function test_it_does_not_add_duplicate_interactive_flags() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker exec --user '$this->uid:$this->gid' --tty -i php-fpm-container-id '/bin/bash'" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( Docker::class );

        $tester = $this->runCommand( $command, [
            'exec',
            '--tty',
            '-i',
            'php-fpm-container-id',
            '/bin/bash',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_does_not_add_duplicate_interactive_options() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker exec --user '$this->uid:$this->gid' --tty --interactive php-fpm-container-id '/bin/bash'" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( Docker::class );

        $tester = $this->runCommand( $command, [
            'exec',
            '--tty',
            '--interactive',
            'php-fpm-container-id',
            '/bin/bash',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

}
