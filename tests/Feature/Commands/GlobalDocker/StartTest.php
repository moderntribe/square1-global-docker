<?php

namespace Tests\Feature\Commands\GlobalDocker;

use App\Commands\DockerCompose;
use App\Commands\GlobalDocker\Start;
use App\Services\Docker\Dns\Handler;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Commands\BaseCommandTester;

class StartTest extends BaseCommandTester {

    protected $resolveHandler;
    protected $dockerCompose;

    protected function setUp(): void {
        parent::setUp();

        $this->resolveHandler = $this->mock( Handler::class );

        $this->dockerCompose = $this->mock( DockerCompose::class );
        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            Start::PROJECT_NAME,
            'up',
            '--remove-orphans',
            '-d',
        ] );

        Artisan::swap( $this->dockerCompose );
    }

    public function test_it_starts_global_containers_with_existing_resolver() {
        $this->resolveHandler->shouldReceive( 'enabled' )->once()->andReturn( true );

        $command = $this->app->make( Start::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Starting global docker containers...', $tester->getDisplay() );
    }

    public function test_it_starts_global_containers_with_no_resolver() {
        $this->resolveHandler->shouldReceive( 'enabled' )->once()->andReturn( false );
        $this->resolveHandler->shouldReceive( 'enable' )->once();

        $command = $this->app->make( Start::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Starting global docker containers...', $tester->getDisplay() );
        $this->assertStringContainsString( 'DNS resolvers not enabled! Enter your sudo password when requested to enable them...', $tester->getDisplay() );
    }

}
