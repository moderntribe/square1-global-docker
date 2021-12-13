<?php declare( strict_types=1 );

namespace Tests\Unit\Services\Docker;

use App\Runners\CommandRunner;
use App\Services\Docker\Container;
use App\Services\Docker\Local\Config;
use Tests\TestCase;

final class ContainerTest extends TestCase {

    /**
     * @var \App\Contracts\Runner
     */
    private $runner;

    /**
     * @var Config
     */
    private $config;

    private $container;

    protected function setUp(): void {
        parent::setUp();

        $this->runner    = $this->mock( CommandRunner::class );
        $this->config    = $this->mock( Config::class );
        $this->container = new Container( $this->runner, $this->config );
    }

    public function test_it_gets_the_php_fpm_container_id() {
        $this->config->shouldReceive( 'getProjectName' )
                     ->once()
                     ->andReturn( 'square1' );

        $this->config->shouldReceive( 'getDockerDir' )
                     ->once()
                     ->andReturn( '/tmp/square1' );

        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [
                         'project'   => 'square1',
                         'container' => 'php-fpm',
                         'dockerDir' => '/tmp/square1',
                     ] )->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker-compose --project-directory {{ $dockerDir }} --project-name {{ $project }} ps -q {{ $container }}' )
                     ->once()
                     ->andReturnSelf();

        $this->container->getId();
    }

    public function test_it_gets_a_custom_container_id() {
        $this->config->shouldReceive( 'getProjectName' )
                     ->once()
                     ->andReturn( 'square1' );

        $this->config->shouldReceive( 'getDockerDir' )
                     ->once()
                     ->andReturn( '/tmp/square1' );

        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [
                         'project'   => 'square1',
                         'container' => 'php-tests',
                         'dockerDir' => '/tmp/square1',
                     ] )->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker-compose --project-directory {{ $dockerDir }} --project-name {{ $project }} ps -q {{ $container }}' )
                     ->once()
                     ->andReturnSelf();

        $this->container->getId( 'php-tests' );
    }

}
