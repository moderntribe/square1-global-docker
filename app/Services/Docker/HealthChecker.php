<?php declare( strict_types=1 );

namespace App\Services\Docker;

use App\Contracts\Runner;
use TitasGailius\Terminal\Response;

/**
 * Check the health for docker containers.
 *
 * @package App\Services\Docker
 */
class HealthChecker {

    public const HEALTHY = 'healthy';

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    public function __construct( Runner $runner ) {
        $this->runner = $runner;
    }

    /**
     * Check if a container running docker's health check has become healthy.
     *
     * @param string $containerName The container name to check, as shown in "docker ps".
     */
    public function healthy( string $containerName ): bool {
        $command = 'docker ps --filter {{ $container }} --filter "health=healthy" --format "{{ .Status }}"';

        /** @var Response $response */
        $response = $this->runner->with( [
            'container' => "name=$containerName",
        ] )->command( $command )->run();

        return str_contains( (string) $response, self::HEALTHY );
    }

}
