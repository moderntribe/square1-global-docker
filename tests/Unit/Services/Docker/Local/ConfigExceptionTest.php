<?php

namespace Tests\Unit\Services\Docker\Local;

use App\Runners\CommandRunner;
use App\Services\Docker\Local\Config;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Storage;
use phpmock\mockery\PHPMockery;
use RuntimeException;
use Tests\TestCase;

final class ConfigExceptionTest extends TestCase {

    private $runner;
    private $config;

    protected function setUp(): void {
        parent::setUp();

        // Prevent Mockery from erroring out on Response::__call
        error_reporting( 0 );

        $this->runner = $this->mock( CommandRunner::class );
        $this->config = $this->mock( Repository::class );
    }

    public function test_it_throws_exception_on_invalid_project_root() {
        $this->expectException( RuntimeException::class );
        $this->expectExceptionMessage( 'Unable to find project root. Are you sure this is a SquareOne Project?' );

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.yml', '' );

        $this->runner->shouldReceive( 'with' )->with( [ 'path' => '' ] )->andReturnSelf();

        // Mock we already hit the operating system's root folder
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( '/' );

        $config = new Config( $this->runner, $this->config );

        $config->getProjectRoot();
    }

}
