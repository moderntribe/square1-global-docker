<?php declare( strict_types=1 );

namespace Tests\Feature\Commands;

use App\Commands\DockerCompose;
use App\Contracts\ArgumentRewriter;
use App\Runners\CommandRunner;
use App\Services\Docker\Network;
use Symfony\Component\Process\Process;

final class DockerComposeTest extends BaseCommandTester {

    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $process = $this->mock( Process::class );
        $process->shouldReceive( 'getOutput' )->andReturn( 'docker output would be here...' );

        $this->runner = $this->mock( CommandRunner::class );
        $this->runner->shouldReceive( 'output' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'withEnvironmentVariables' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'process' )->once()->andReturn( $process );

        $network = $this->mock( Network::class );
        $network->shouldReceive( 'getGateWayIP' )->with()->once()->andReturn( '172.17.0.1' );
    }

    public function test_it_can_proxy_docker_compose_commands() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker-compose --project-name test --file '/tmp/docker-compose.yml' up" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( DockerCompose::class );

        $tester = $this->runCommand( $command, [
            '--project-name',
            'test',
            '--file',
            '/tmp/docker-compose.yml',
            'up',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_can_proxy_version_options() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( sprintf(
                         "docker-compose --project-name test --file '/tmp/docker-compose.yml' exec php %s",
                         ArgumentRewriter::OPTION_VERSION )
                     )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( DockerCompose::class );

        $tester = $this->runCommand( $command, [
            '--project-name',
            'test',
            '--file',
            '/tmp/docker-compose.yml',
            'exec',
            'php',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_can_proxy_version_flags() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( sprintf(
                         "docker-compose --project-name test --file '/tmp/docker-compose.yml' exec composer %s",
                         ArgumentRewriter::FLAG_VERSION )
                     )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( DockerCompose::class );

        $tester = $this->runCommand( $command, [
            '--project-name',
            'test',
            '--file',
            '/tmp/docker-compose.yml',
            'exec',
            'composer',
            ArgumentRewriter::FLAG_VERSION_PROXY,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_can_disable_tty() {
        $this->runner->shouldReceive( 'tty' )->with( false )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker-compose --project-name test --file '/tmp/docker-compose.yml' exec -T '/bin/bash'" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( DockerCompose::class );

        $tester = $this->runCommand( $command, [
            '--project-name',
            'test',
            '--file',
            '/tmp/docker-compose.yml',
            'exec',
            '-T',
            '/bin/bash'
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_returns_failed_exit_code_on_bad_command() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker-compose --project-name test --file '/tmp/docker-compose.yml' invalid-command" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnFalse();

        $command = $this->app->make( DockerCompose::class );

        $tester = $this->runCommand( $command, [
            '--project-name',
            'test',
            '--file',
            '/tmp/docker-compose.yml',
            'invalid-command',
        ] );

        $this->assertSame( 1, $tester->getStatusCode() );
    }

    public function test_it_can_run_an_alternate_docker_compose_binary() {
        $this->runner->shouldReceive( 'tty' )->with( true )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( "docker compose --project-name test --file '/tmp/docker-compose.yml' up" )
                     ->once()
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'ok' )->once()->andReturnTrue();

        $command = $this->app->make( DockerCompose::class, [ 'binary' => 'docker compose' ] );

        $tester = $this->runCommand( $command, [
            '--project-name',
            'test',
            '--file',
            '/tmp/docker-compose.yml',
            'up',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

}
