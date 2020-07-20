<?php

namespace Tests\Unit\Services\Docker;

use Mockery;
use App\Runners\CommandRunner;
use App\Services\Docker\Network;
use App\Services\OperatingSystem;
use Mockery\Mock;
use Tests\TestCase;

class NetworkTest extends TestCase {

    private $runner;
    private $os;

    protected function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
        $this->os     = $this->mock( OperatingSystem::class );
    }

    public function test_it_gets_docker_ip_on_linux() {
        $this->os->shouldReceive( 'getFamily' )->andReturn( OperatingSystem::LINUX );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker network inspect bridge' )
                     ->once()
                     ->andReturn( $this->runner );

        $this->runner->shouldReceive( 'throw' )->once()->andReturn( $this->runner );

        $this->runner->shouldReceive( '__toString' )
                     ->once()
                     ->andReturn( $this->getDockerNetworkInspectOutput() );

        $network = new Network( $this->os, $this->runner );

        $ip = $network->getGateWayIP();

        $this->assertEquals( '172.17.0.1', $ip );
    }

    public function test_it_gets_docker_ip_on_osx() {
        $this->os->shouldReceive( 'getFamily' )->andReturn( OperatingSystem::MAC_OS );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --rm -t alpine:3.11.5 nslookup host.docker.internal. | grep "Address:" | awk \'{ print $2 }\' | tail -1' )
                     ->once()
                     ->andReturn( $this->runner );

        $this->runner->shouldReceive( 'throw' )
                     ->once()
                     ->andReturn( $this->runner );

        $this->runner->shouldReceive( '__toString' )
                     ->once()
                     ->andReturn( '172.1.20.0' );

        $network = new Network( $this->os, $this->runner );

        $ip = $network->getGateWayIP();

        $this->assertEquals( '172.1.20.0', $ip );
    }

    public function testItCanGetTheDockerGatewayIpInLinux() {
        $os = $this->partialMock( OperatingSystem::class );
        $os->shouldReceive( 'getFamily' )->andReturn( OperatingSystem::LINUX );

        $mock = Mockery::mock( Network::class, [ $os, $this->runner ] )->makePartial();
        $mock->shouldAllowMockingProtectedMethods()->shouldReceive( 'getLinuxGatewayIP' )->once()->andReturn( '172.1.20.0' );

        $this->assertEquals( '172.1.20.0', $mock->getGateWayIP() );
    }

    public function testItCanGetTheDockerGatewayIpInMacOs() {
        $os = $this->partialMock( OperatingSystem::class );
        $os->shouldReceive( 'getFamily' )->andReturn( OperatingSystem::MAC_OS );

        $mock = Mockery::mock( Network::class, [ $os, $this->runner ] )->makePartial();
        $mock->shouldAllowMockingProtectedMethods()->shouldReceive( 'getMacOSGatewayIP' )->once()->andReturn( '172.1.20.0' );

        $this->assertEquals( '172.1.20.0', $mock->getGateWayIP() );
    }

    /**
     * Mocked "docker network inspect bridge" output.
     *
     * @return string
     */
    protected function getDockerNetworkInspectOutput() {
        return '[
    {
        "Name": "bridge",
        "Id": "2cdb318cce128f3ab2602efab0a9bc6628df194e54b9aceb4483b29acf76ad00",
        "Created": "2020-06-12T20:35:47.723895089-06:00",
        "Scope": "local",
        "Driver": "bridge",
        "EnableIPv6": false,
        "IPAM": {
            "Driver": "default",
            "Options": null,
            "Config": [
                {
                    "Subnet": "172.17.0.0/16",
                    "Gateway": "172.17.0.1"
                }
            ]
        },
        "Internal": false,
        "Attachable": false,
        "Ingress": false,
        "ConfigFrom": {
            "Network": ""
        },
        "ConfigOnly": false,
        "Containers": {},
        "Options": {
            "com.docker.network.bridge.default_bridge": "true",
            "com.docker.network.bridge.enable_icc": "true",
            "com.docker.network.bridge.enable_ip_masquerade": "true",
            "com.docker.network.bridge.host_binding_ipv4": "0.0.0.0",
            "com.docker.network.bridge.name": "docker0",
            "com.docker.network.driver.mtu": "1500"
        },
        "Labels": {}
    }
]
';
    }

}
