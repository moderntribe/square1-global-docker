<?php declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\PermissionManager;
use Tests\TestCase;
use App\Services\OperatingSystem;
use phpmock\mockery\PHPMockery;

final class PermissionManagerTest extends TestCase {

    /**
     * @var \Mockery\MockInterface|OperatingSystem
     */
    private $os;

    protected function setUp(): void {
        parent::setUp();

        $this->os = $this->mock( OperatingSystem::class );
    }

    public function test_it_sets_user_and_group_info_for_a_non_standard_linux_user(): void {
        $uid = 500;
        $gid = 501;

        PHPMockery::mock( 'App\Services', 'getmyuid' )->andReturn( $uid );
        PHPMockery::mock( 'App\Services', 'getmygid' )->andReturn( $gid );

        $this->os->shouldReceive( 'getFamily' )->andReturn( OperatingSystem::LINUX );

        $permissionManager = new PermissionManager( $this->os );

        $this->assertSame( $uid, $permissionManager->uid() );
        $this->assertSame( $gid, $permissionManager->gid() );
    }

    public function test_it_sets_user_and_group_info_for_linux(): void {
        $uid = getmyuid();
        $gid = getmygid();

        $this->os->shouldReceive( 'getFamily' )->andReturn( OperatingSystem::LINUX );

        $permissionManager = new PermissionManager( $this->os );

        $this->assertSame( $uid, $permissionManager->uid() );
        $this->assertSame( $gid, $permissionManager->gid() );
    }

    public function test_it_sets_user_and_group_info_for_non_linux(): void {
        $this->os->shouldReceive( 'getFamily' )->andReturn( OperatingSystem::MAC_OS );

        $permissionManager = new PermissionManager( $this->os );

        $this->assertSame( PermissionManager::DEFAULT_UID, $permissionManager->uid() );
        $this->assertSame( PermissionManager::DEFAULT_GID, $permissionManager->gid() );
    }

}
