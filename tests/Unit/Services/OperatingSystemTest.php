<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use phpmock\mockery\PHPMockery;
use App\Services\OperatingSystem;

/**
 * Class OperatingSystemTest
 *
 * @see https://gist.github.com/natefoo/814c5bf936922dad97ff
 *
 * @package Tests\Unit\Services
 */
class OperatingSystemTest extends TestCase {

    private $os;

    protected function setUp(): void {
        parent::setUp();

        $this->os = new OperatingSystem();
    }

    public function test_os_family() {
        $os = $this->partialMock( OperatingSystem::class );
        $os->shouldAllowMockingProtectedMethods()->shouldReceive( 'getFamily' )->andReturn( 'Linux' );

        $this->assertSame( 'Linux', $this->os->getFamily() );
    }

    public function test_arch_linux_flavor() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->with( OperatingSystem::OS_RELEASE )->once()->andReturnTrue();
        PHPMockery::mock( 'App\Services', 'file_get_contents' )->andReturn( 'ID=arch' );

        $this->assertSame( 'Arch', $this->os->getLinuxFlavor() );
    }

    public function test_ubuntu_1404_linux_flavor() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->with( OperatingSystem::OS_RELEASE )->once()->andReturnTrue();
        PHPMockery::mock( 'App\Services', 'file_get_contents' )->andReturn( "ID=ubuntu\r\nID_LIKE=debian" );

        $this->assertSame( 'Debian', $this->os->getLinuxFlavor() );
    }

    public function test_debian_linux_flavor() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->with( OperatingSystem::OS_RELEASE )->once()->andReturnTrue();
        PHPMockery::mock( 'App\Services', 'file_get_contents' )->andReturn( 'ID=debian' );

        $this->assertSame( 'Debian', $this->os->getLinuxFlavor() );
    }

    public function test_centos_linux_flavor() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->with( OperatingSystem::OS_RELEASE )->once()->andReturnTrue();
        PHPMockery::mock( 'App\Services', 'file_get_contents' )->andReturn( "ID=\"centos\"\r\nID_LIKE=\"rhel fedora\"" );

        $this->assertSame( 'Rhel fedora', $this->os->getLinuxFlavor() );
    }

    public function test_fedora_linux_flavor() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->with( OperatingSystem::OS_RELEASE )->once()->andReturnTrue();
        PHPMockery::mock( 'App\Services', 'file_get_contents' )->andReturn( 'ID=fedora' );

        $this->assertSame( 'Fedora', $this->os->getLinuxFlavor() );
    }

    public function test_opensuse_linux_flavor() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->with( OperatingSystem::OS_RELEASE )->once()->andReturnTrue();
        PHPMockery::mock( 'App\Services', 'file_get_contents' )->andReturn( 'ID=opensuse' );

        $this->assertSame( 'Opensuse', $this->os->getLinuxFlavor() );
    }

    public function test_shell_exec_fallback() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->with( OperatingSystem::OS_RELEASE )->once()->andReturnFalse();
        PHPMockery::mock( 'App\Services', 'shell_exec' )->with( 'lsb_release -is')->once()->andReturn( 'ManjaroLinux' );

        $this->assertSame( 'Manjaro', $this->os->getLinuxFlavor() );
    }

    public function test_unknown_operating_system() {
        PHPMockery::mock( 'App\Services', 'is_readable' )->andReturnFalse();
        PHPMockery::mock( 'App\Services', 'shell_exec' )->with( 'lsb_release -is')->once()->andReturn( '' );

        $this->assertEmpty( $this->os->getLinuxFlavor() );
    }

}
