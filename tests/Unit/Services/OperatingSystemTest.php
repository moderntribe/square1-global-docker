<?php

namespace Tests\Unit\Services;

use App\Services\OperatingSystem;
use Mockery\Mock;
use Tests\TestCase;

class OperatingSystemTest extends TestCase {

    private $os;

    protected function setUp(): void {
        parent::setUp();

        $this->os = $this->partialMock( OperatingSystem::class );
    }

    public function test_os_family() {
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'getFamily' )->andReturn( 'Linux' );

        $this->assertSame( 'Linux', $this->os->getFamily() );
    }

    public function test_arch_linux_flavor() {
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'readOsRelease' )->andReturn( 'Arch' );

        $this->assertSame( 'Arch', $this->os->getLinuxFlavor() );
    }

    public function test_ubuntu_linux_flavor() {
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'readOsRelease' )->andReturn( 'Ubuntu' );

        $this->assertSame( 'Ubuntu', $this->os->getLinuxFlavor() );
    }

    public function test_debian_linux_flavor() {
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'readOsRelease' )->andReturn( 'Debian' );

        $this->assertSame( 'Debian', $this->os->getLinuxFlavor() );
    }

    public function test_zorin_flavor() {
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'readOsRelease' )->andReturn( 'Ubuntu' );

        $this->assertSame( 'Ubuntu', $this->os->getLinuxFlavor() );
    }

    public function testShellExecFallback() {
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'readOsRelease' )->andReturn( '' );
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'getLsbRelease' )->andReturn( 'Description:	Manjaro Linux' );

        $this->assertSame( 'Manjaro', $this->os->getLinuxFlavor() );
    }

    public function test_unknown_operating_system() {
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'readOsRelease' )->andReturn( '' );
        $this->os->shouldAllowMockingProtectedMethods()->shouldReceive( 'getLsbRelease' )->andReturn( '' );

        $this->assertEmpty( $this->os->getLinuxFlavor() );
    }

}
