<?php

namespace Tests\Feature\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use App\Commands\GlobalDocker\Logs;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Commands\BaseCommandTest;

class LogsTest extends BaseCommandTest {

    private $dockerCompose;

    public function setUp(): void {
        parent::setUp();

        $this->dockerCompose = $this->mock( DockerCompose::class );
    }

    public function test_it_runs_global_logs_command() {
        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            Logs::PROJECT_NAME,
            '--file',
            config( 'squareone.docker.compose' ),
            'logs',
            '-f',
        ] );

        Artisan::swap( $this->dockerCompose );

        $command = $this->app->make( Logs::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

}
