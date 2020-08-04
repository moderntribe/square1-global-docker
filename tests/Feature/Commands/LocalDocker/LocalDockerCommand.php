<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Commands\BaseCommandTester;

class LocalDockerCommand extends BaseCommandTester {

    protected $project;
    protected $dockerDir;
    protected $composeFile;
    protected $config;
    protected $dockerCompose;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests/dev/docker' );

        $this->project       = 'squareone';
        $this->dockerDir     = storage_path( 'tests/dev/docker' );
        $this->composeFile   = storage_path( 'tests/dev/docker/docker-compose.yml' );
        $this->config        = $this->mock( Config::class );
        $this->dockerCompose = $this->mock( DockerCompose::class );
    }

}
