<?php

namespace Tests\Feature\Commands;

use App\Commands\DockerCompose;
use App\Runners\CommandRunner;
use App\Services\Docker\Network;
use Symfony\Component\Process\Process;

class DockerComposeTest extends BaseCommandTester {

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

    public function testItCanProxyDockerComposeCommands() {
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

    public function testItCanDisableTty() {
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

    public function testItReturnsFailedExitCodeOnBadCommand() {
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

}
