<?php declare( strict_types=1 );

namespace App\Services\Docker;

use App\Contracts\Runner;
use App\Services\Docker\Local\Config;

/**
 * Class Container
 *
 * @package App\Services\Docker
 */
class Container {

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * The docker configuration.
     *
     * @var \App\Services\Docker\Local\Config
     */
    protected $config;

    /**
     * @param  \App\Contracts\Runner              $runner
     * @param  \App\Services\Docker\Local\Config  $config
     */
    public function __construct( Runner $runner, Config $config ) {
        $this->runner = $runner;
        $this->config = $config;
    }

    /**
     * Get the container ID from a project via docker compose.
     *
     * @param  string  $container  The container name in docker-compose.yml.
     *
     * @return string
     */
    public function getId( string $container = 'php-fpm' ): string {
        $response = $this->runner->with( [
            'project'   => $this->config->getProjectName(),
            'container' => $container,
            'dockerDir' => $this->config->getDockerDir(),
        ] )->run( 'docker-compose --project-directory {{ $dockerDir }} --project-name {{ $project }} ps -q {{ $container }}' )->throw();

        return trim( (string) $response );
    }

}
