<?php

namespace Tests\Unit\Services\Docker\Local;

use Tests\TestCase;
use App\Runners\CommandRunner;
use TitasGailius\Terminal\Response;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Storage;

class ConfigTest extends TestCase {

    protected $runner;
    protected $response;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.yml', '' );

        // Prevent Mockery from erroring out on Response::__call
        error_reporting( 0 );

        $this->response = $this->mock( Response::class );

        $this->runner = $this->mock( CommandRunner::class );

        $this->runner->shouldReceive( 'with' )
                     ->with( [
                         'path' => '',
                     ] )->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->with( 'git -C {{ $path }} rev-parse --show-superproject-working-tree' )
                     ->andReturn( $this->response );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'git -C {{ $path }} rev-parse --show-toplevel' )
                     ->andReturn( $this->response );
    }

    public function test_it_can_set_a_path() {
        $this->runner->shouldReceive( 'with' )
                     ->with( [
                         'path' => storage_path( 'tests/squareone' ),
                     ] )->andReturnSelf();

        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $config = $config->setPath( storage_path( 'tests/squareone' ) );

        $this->assertSame( storage_path( 'tests/squareone' ), $config->getProjectRoot() );
    }

    public function test_it_gets_a_project_root() {
        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $root = $config->getProjectRoot();

        $this->assertSame( storage_path( 'tests/squareone' ), $root );
    }

    public function test_it_gets_a_project_root_for_submodule() {
        // Found via sub module
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $root = $config->getProjectRoot();

        $this->assertSame( storage_path( 'tests/squareone' ), $root );
    }

    public function test_it_gets_a_project_root_in_sub_git_repo() {
        Storage::disk( 'local' )->put( 'tests/squareone_with_sub_repo/dev/docker/docker-compose.yml', '' );
        Storage::disk( 'local' )->makeDirectory( 'tests/squareone_with_sub_repo/sub_repo' );

        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone_with_sub_repo/sub_repo' ) );

        // With no dev/docker/docker-compose.yml found in the above directory, expect the next loop
        $this->runner->shouldReceive( 'with' )
                     ->with( [
                         'path' => storage_path( 'tests/squareone_with_sub_repo' ),
                     ] )->andReturnSelf();

        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone_with_sub_repo' ) );

        $config = new Config( $this->runner );

        $root = $config->getProjectRoot();

        $this->assertSame( storage_path( 'tests/squareone_with_sub_repo' ), $root );
    }

    public function test_it_finds_docker_compose_yml() {
        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $compose = $config->getComposeFile();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker/docker-compose.yml' ), $compose );
    }

    public function test_it_finds_docker_compose_override_yml() {
        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.override.yml', '' );

        $config = new Config( $this->runner );

        $compose = $config->getComposeFile();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker/docker-compose.override.yml' ), $compose );
    }

    public function test_it_gets_a_project_name() {
        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        $config = new Config( $this->runner );

        $name = $config->getProjectName();

        $this->assertSame( 'squareone', $name );
    }

    public function test_it_gets_project_domain() {
        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        $config = new Config( $this->runner );

        $domain = $config->getProjectDomain();

        $this->assertSame( 'squareone.tribe', $domain );

        $domain = $config->getProjectDomain( 'com' );

        $this->assertSame( 'squareone.com', $domain );
    }

    public function test_it_gets_project_url() {
        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        $config = new Config( $this->runner );

        $url = $config->getProjectUrl();

        $this->assertSame( 'https://squareone.tribe', $url );

        $url = $config->getProjectUrl( 'com', 'http' );

        $this->assertSame( 'http://squareone.com', $url );
    }

    public function test_it_gets_composer_volume() {
        // No submodule found
        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( '' );

        $this->response->shouldReceive( 'ok' )
                       ->once()
                       ->andReturnSelf();

        $this->response->shouldReceive( '__toString' )
                       ->once()
                       ->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $root = $config->getComposerVolume();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker/composer' ), $root );
    }

}
