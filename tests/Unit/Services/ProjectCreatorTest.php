<?php

namespace Tests\Unit\Services;

use App\Services\ProjectCreator;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class ProjectCreatorTest extends TestCase {

    private $filesystem;
    private $creator;

    protected function setUp(): void {
        parent::setUp();

        $this->filesystem = $this->mock( Filesystem::class );
        $this->creator    = $this->app->make( ProjectCreator::class );
    }

    public function test_it_sets_a_project_id() {
        $this->filesystem->shouldReceive( 'replace' )
                         ->once()->with( 'squareone/dev/docker/.projectID', 'squareone' );

        $this->creator->setProjectId( 'squareone' );
    }

    public function test_it_updates_nginx_conf() {
        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( 'squareone/dev/docker/nginx/nginx.conf' )
                         ->andReturn( 'nginx.conf content square1.tribe' );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( 'squareone/dev/docker/nginx/nginx.conf', 'nginx.conf content squareone.tribe' );

        $this->creator->updateNginxConf( 'squareone' );
    }

    public function test_it_updates_docker_compose_yml() {
        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( 'squareone/dev/docker/docker-compose.yml' )
                         ->andReturn( 'square1.tribe square1test.tribe tribe_square1 tribe_square1tests' );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( 'squareone/dev/docker/docker-compose.yml', 'squareone.tribe squareonetest.tribe tribe_squareone tribe_squareonetests' );

        $this->creator->updateDockerCompose( 'squareone' );
    }

    public function test_it_updates_wp_cli_yml() {
        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( 'squareone/dev/docker/wp-cli.yml' )
                         ->andReturn( 'url: square1.tribe' );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( 'squareone/dev/docker/wp-cli.yml', 'url: squareone.tribe' );

        $this->creator->updateWpCli( 'squareone' );
    }

    public function test_it_updates_github_workflows_ci() {
        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( 'squareone/.github/workflows/ci.yml' )
                         ->andReturn( 'PROJECT_ID: square1 CREATE DATABASE tribe_square1_tests' );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( 'squareone/.github/workflows/ci.yml', 'PROJECT_ID: squareone CREATE DATABASE tribe_squareone_tests' );

        $this->creator->updateGitWorkflows( 'squareone' );
    }

    public function test_it_updates_codeception_config() {
        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( 'squareone/dev/tests/.env-dist' )
                         ->andReturn( 'WP_URL="http://square1test.tribe" TEST_DB_NAME="tribe_square1_tests" ACCEPTANCE_DB_NAME=tribe_square1_acceptance' );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( 'squareone/dev/tests/.env-dist', 'WP_URL="http://squareonetest.tribe" TEST_DB_NAME="tribe_squareone_tests" ACCEPTANCE_DB_NAME=tribe_squareone_acceptance' );

        $this->filesystem->shouldReceive( 'copy' )->once()->with( 'squareone/dev/tests/.env-dist', 'squareone/dev/tests/.env' );

        $this->creator->updateCodeceptionConfig( 'squareone' );
    }

    public function test_it_updates_codeception_dump_sql() {
        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( 'squareone/dev/tests/tests/_data/dump.sql' )
                         ->andReturn( "(2,'siteurl','http://square1test.tribe','yes'), (3,'home','http://square1test.tribe','yes')" );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( 'squareone/dev/tests/tests/_data/dump.sql', "(2,'siteurl','http://squareonetest.tribe','yes'), (3,'home','http://squareonetest.tribe','yes')" );

        $this->creator->updateTestDumpSql( 'squareone' );
    }

}
