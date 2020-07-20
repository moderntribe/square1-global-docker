<?php

namespace Tests\Unit\Services\Docker\Local;

use Tests\TestCase;
use RuntimeException;
use App\Runners\CommandRunner;
use TitasGailius\Terminal\Response;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Storage;

class ConfigExceptionTest extends TestCase {

    private $runner;
    private $response;

    protected function setUp(): void {
        parent::setUp();

        // Prevent Mockery from erroring out on Response::__call
        error_reporting( 0 );

        $this->runner = $this->mock( CommandRunner::class );
        $this->response = $this->mock( Response::class );
    }

    public function test_it_throws_exception_on_invalid_project_root() {
        $this->expectException( RuntimeException::class );
        $this->expectExceptionMessage( 'Unable to find project root. Are you sure this is a SquareOne Project?' );

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.yml', '' );

        $this->response->shouldReceive( 'ok' )->andReturnFalse();

        $this->runner->shouldReceive( 'with' )->with( [ 'path' => '' ] )->andReturnSelf();
        $this->runner->shouldReceive( 'run' )->with( 'git -C {{ $path }} rev-parse --show-toplevel' )->andReturn( $this->response );

        $config = new Config( $this->runner );

        $config->getProjectRoot();
    }

    public function test_it_throws_exception_on_invalid_docker_compose_file() {
        $invalidPath = storage_path( 'tests/invalid-squareone-project' );

        $this->expectException( RuntimeException::class );
        $this->expectExceptionMessage( 'Unable to find /dev/docker/docker-compose.yml or ./squareone.yml in ' . $invalidPath
                                       . '. Are you sure this is a SquareOne Project?' );

        $this->response->shouldReceive( 'ok' )->andReturnSelf();
        $this->response->shouldReceive( '__toString' )->once()->andReturn( $invalidPath );

        $this->runner->shouldReceive( 'with' )->with( [ 'path' => '' ] )->andReturnSelf();
        $this->runner->shouldReceive( 'run' )->with( 'git -C {{ $path }} rev-parse --show-toplevel' )->andReturn( $this->response );

        $config = new Config( $this->runner );

        $config->getProjectRoot();
    }

}
