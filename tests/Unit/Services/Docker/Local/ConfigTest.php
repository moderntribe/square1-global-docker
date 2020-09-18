<?php

namespace Tests\Unit\Services\Docker\Local;

use Tests\TestCase;
use App\Runners\CommandRunner;
use phpmock\mockery\PHPMockery;
use TitasGailius\Terminal\Response;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Storage;

class ConfigTest extends TestCase {

    protected $runner;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.yml', '' );

        // Prevent Mockery from erroring out on Response::__call
        error_reporting( 0 );

        $this->runner = $this->mock( CommandRunner::class );

        $this->runner->shouldReceive( 'with' )
                     ->with( [
                         'path' => '',
                     ] )->andReturnSelf();
    }

    public function test_it_can_set_a_path() {
        $this->runner->shouldReceive( 'with' )
                     ->with( [
                         'path' => storage_path( 'tests/squareone' ),
                     ] )->andReturnSelf();

        $config = new Config( $this->runner );

        $config = $config->setPath( storage_path( 'tests/squareone' ) );

        $this->assertSame( storage_path( 'tests/squareone' ), $config->getProjectRoot() );
    }

    public function test_it_gets_a_project_root() {
        // Mock getcwd() found our tests storage path
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $root = $config->getProjectRoot();

        $this->assertSame( storage_path( 'tests/squareone' ), $root );
    }

    public function test_it_finds_docker_compose_yml() {
        // Mock getcwd() found our tests storage path
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $compose = $config->getDockerDir();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker' ), $compose );
    }

    public function test_it_finds_docker_compose_override_yml() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.override.yml', '' );

        // Mock getcwd() found our tests storage path
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $compose = $config->getDockerDir();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker' ), $compose );
    }

    public function test_it_gets_a_project_name() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        // Mock getcwd() found our tests storage path
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $name = $config->getProjectName();

        $this->assertSame( 'squareone', $name );
    }

    public function test_it_gets_project_domain() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        // Mock getcwd() found our tests storage path
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $domain = $config->getProjectDomain();

        $this->assertSame( 'squareone.tribe', $domain );

        $domain = $config->getProjectDomain( 'com' );

        $this->assertSame( 'squareone.com', $domain );
    }

    public function test_it_gets_project_url() {
        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/.projectID', 'squareone' );

        // Mock getcwd() found our tests storage path
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( storage_path( 'tests/squareone' ) );

        $config = new Config( $this->runner );

        $url = $config->getProjectUrl();

        $this->assertSame( 'https://squareone.tribe', $url );

        $url = $config->getProjectUrl( 'com', 'http' );

        $this->assertSame( 'http://squareone.com', $url );
    }

    public function test_it_gets_composer_volume() {
        $config = new Config( $this->runner );

        // Mock getcwd() found our tests storage path
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( storage_path( 'tests/squareone' ) );

        $root = $config->getComposerVolume();

        $this->assertSame( storage_path( 'tests/squareone/dev/docker/composer' ), $root );
    }

}
