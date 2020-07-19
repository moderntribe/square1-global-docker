<?php declare( strict_types=1 );

namespace App\Services\Docker;

use App\Contracts\Runner;
use App\Services\OperatingSystem;

/**
 * Docker Network
 *
 * @package App\Services\Docker
 */
class Network {

    /**
     * The os service.
     *
     * @var OperatingSystem
     */
    protected $os;

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * Network constructor.
     *
     * @param  OperatingSystem        $os
     * @param  \App\Contracts\Runner  $runner
     */
    public function __construct( OperatingSystem $os, Runner $runner ) {
        $this->os = $os;
        $this->runner = $runner;
    }

    /**
     * Get Docker's Gateway IP address
     *
     * @return string|null
     */
    public function getGateWayIP(): ?string {
        if ( OperatingSystem::MAC_OS === $this->os->getFamily() ) {
            return $this->getMacOSGatewayIP();
        }

        return $this->getLinuxGatewayIP();
    }

    /**
     * Get the docker gateway IP address in linux.
     *
     * @return string|null The IP address.
     */
    protected function getLinuxGatewayIP(): ?string {
        $response = $this->runner->run( 'docker network inspect bridge' )->throw();

        $data = json_decode( trim( (string) $response ), true );

        $ip = $data[0]['IPAM']['Config'][0]['Gateway'] ?? '';

        return filter_var( $ip, FILTER_VALIDATE_IP ) ?: null;
    }

    /**
     * Get the docker gateway IP address in Mac OS.
     *
     * @return string The IP address.
     */
    protected function getMacOSGatewayIP(): string {
        $response = $this->runner->run( 'docker run --rm -t alpine:3.11.5 nslookup host.docker.internal. | grep "Address:" | awk \'{ print $2 }\' | tail -1' )
                                 ->throw();

        return trim( (string) $response );
    }
}
