<?php

namespace Tests\Unit\Services\Docker\Local;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Services\Docker\Local\Config;
use TitasGailius\Terminal\Response;

class ConfigTest extends TestCase {

    protected $runner;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.yml', '' );

        // Prevent Mockery from erroring out on Response::__call
        error_reporting( 0 );

        $response = $this->mock( Response::class );

        $response->shouldReceive( 'ok' )
                 ->once()
                 ->andReturnSelf();

        $response->shouldReceive( '__toString' )
                 ->once()
                 ->andReturn( storage_path( 'tests/squareone' ) );

        $this->runner = $this->mock( CommandRunner::class );

        $this->runner->shouldReceive( 'with' )
                     ->with( [
                         'path' => '',
                     ] )->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->with( 'git -C {{ $path }} rev-parse --show-toplevel' )
                     ->andReturn( $response );
    }

    public function testItCanSetAPath() {
        $this->runner->shouldReceive( 'with' )
                     ->with( [
                         'path' => storage_path( 'tests/squareone' ),
                     ] )->andReturnSelf();

        $config = new Config( $this->runner );

        $config = $config->setPath( storage_path( 'tests/squareone' ) );

        $this->assertSame( storage_path( 'tests/squareone' ), $config->getProjectRoot() );
    }

    public function test_it_gets_a_project_root() {

        $config = new Config( $this->runner );

        $root = $config->getProjectRoot();

        $this->assertSame( storage_path( 'tests/squareone' ), $root );
    }

    public function test_it_finds_docker_compose_yml() {
        $config = new Config( $this->runner );

        $compose = $config->getComposeFile();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker/docker-compose.yml' ), $compose );
    }

    public function test_it_finds_docker_compose_override_yml() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.override.yml', '' );

        $config = new Config( $this->runner );

        $compose = $config->getComposeFile();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker/docker-compose.override.yml' ), $compose );
    }

    public function test_it_gets_a_project_name() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        $config = new Config( $this->runner );

        $name = $config->getProjectName();

        $this->assertSame( 'squareone', $name );
    }

    public function test_it_gets_project_domain() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        $config = new Config( $this->runner );

        $domain = $config->getProjectDomain();

        $this->assertSame( 'squareone.tribe', $domain );

        $domain = $config->getProjectDomain( 'com' );

        $this->assertSame( 'squareone.com', $domain );
    }

    public function test_it_gets_project_url() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        $config = new Config( $this->runner );

        $url = $config->getProjectUrl();

        $this->assertSame( 'https://squareone.tribe', $url );

        $url = $config->getProjectUrl( 'com', 'http' );

        $this->assertSame( 'http://squareone.com', $url );
    }

    public function test_it_gets_composer_volume() {
        $config = new Config( $this->runner );

        $root = $config->getComposerVolume();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker/composer' ), $root );
    }

}
