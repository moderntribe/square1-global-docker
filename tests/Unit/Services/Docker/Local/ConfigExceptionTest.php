<?php

namespace Tests\Unit\Services\Docker\Local;

use Tests\TestCase;
use RuntimeException;
use App\Runners\CommandRunner;
use phpmock\mockery\PHPMockery;
use TitasGailius\Terminal\Response;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Storage;

class ConfigExceptionTest extends TestCase {

    private $runner;

    protected function setUp(): void {
        parent::setUp();

        // Prevent Mockery from erroring out on Response::__call
        error_reporting( 0 );

        $this->runner   = $this->mock( CommandRunner::class );
    }

    public function test_it_throws_exception_on_invalid_project_root() {
        $this->expectException( RuntimeException::class );
        $this->expectExceptionMessage( 'Unable to find project root. Are you sure this is a SquareOne Project?' );

        Storage::disk( 'local' )->put( 'tests/squareone/dev/docker/docker-compose.yml', '' );

        $this->runner->shouldReceive( 'with' )->with( [ 'path' => '' ] )->andReturnSelf();

        // Mock we already hit the operating system's root folder
        PHPMockery::mock( 'App\Services\Docker\Local', 'getcwd' )->andReturn( '/' );

        $config = new Config( $this->runner );

        $config->getProjectRoot();
    }

}
