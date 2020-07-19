<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Services\Docker\Local\Config;
use Tests\Feature\Commands\BaseCommandTest;

class LocalDockerCommand extends BaseCommandTest {

    protected $project;
    protected $composeFile;
    protected $config;
    protected $dockerCompose;

    public function setUp(): void {
        parent::setUp();

        $this->project       = 'squareone';
        $this->composeFile   = storage_path( 'tests/dev/docker/dockercompose.yml' );
        $this->config        = $this->mock( Config::class );
        $this->dockerCompose = $this->mock( DockerCompose::class );
    }

}
