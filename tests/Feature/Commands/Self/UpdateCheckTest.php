<?php

namespace Tests\Feature\Commands\Self;

use App\Commands\Self\UpdateCheck;
use App\Services\Config\Github;
use App\Services\Update\Updater;
use Filebase\Document;
use phpmock\mockery\PHPMockery;
use Tests\Feature\Commands\BaseCommandTest;

class UpdateCheckTest extends BaseCommandTest {

    private $release;
    private $updater;
    private $github;

    public function setUp(): void {
        parent::setUp();

        $this->release = $this->mock( Document::class );
        $this->updater = $this->mock( Updater::class );
        $this->github  = $this->mock( Github::class );
    }

    public function test_it_can_find_a_new_cached_version() {
        $this->release->shouldReceive( 'updatedAt' )->once()->andReturn( date( 'U' ) );

        $this->release->version = '5000';

        $this->updater->shouldReceive( 'getCachedRelease' )->once()->andReturn( $this->release );

        $command = $this->app->make( UpdateCheck::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertEquals( sprintf(
            'A new version "%s" is available! run "so self:update" to update now.',
            $this->release->version
        ), trim( $tester->getDisplay() ) );
    }

    public function test_it_does_not_find_an_update() {
        $this->release->shouldReceive( 'updatedAt' )->once()->andReturn( date( 'U' ) );

        $this->release->version = '0.0.0.1';

        $this->updater->shouldReceive( 'getCachedRelease' )->once()->andReturn( $this->release );

        $command = $this->app->make( UpdateCheck::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertEquals( sprintf( "You're running the latest version: %s",
            $this->app->version()
        ), trim( $tester->getDisplay() ) );
    }

    public function test_it_can_force_an_update() {
        $this->release->shouldReceive( 'updatedAt' )->once()->andReturn( date( 'U' ) );

        $this->release->version = '5000';

        $this->updater->shouldReceive( 'getLatestReleaseFromGitHub' )->once()->andReturn( $this->release );

        $command = $this->app->make( UpdateCheck::class );
        $tester  = $this->runCommand( $command, [
            '--force' => true,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );

        $this->assertEquals( sprintf(
            'A new version "%s" is available! run "so self:update" to update now.',
            $this->release->version
        ), trim( $tester->getDisplay() ) );
    }

    public function test_it_can_handle_empty_release_with_no_default_token() {
        $release = null;

        $this->updater->shouldReceive( 'getLatestReleaseFromGitHub' )->andReturn( $release );

        $this->github->shouldReceive( 'exists' )->once()->andReturnFalse();

        $command = $this->app->make( UpdateCheck::class );
        $tester  = $this->runCommand( $command, [
            '--force' => true,
        ], [
            'Enter your GitHub token to try an authenticated request (it will not be stored):' => 'token',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Unable to fetch update data from the GitHub API', trim( $tester->getDisplay() ) );
        $this->assertStringContainsString( 'An error occurred while checking for update', trim( $tester->getDisplay() ) );
    }

    public function test_it_can_handle_empty_release_with_default_token() {
        PHPMockery::mock( 'App\Commands\Self', 'json_decode' )->andReturn( [
            'github-oauth' => [
                'github.com' => 'my-token',
            ],
        ] );

        $release = null;

        $this->updater->shouldReceive( 'getLatestReleaseFromGitHub' )->andReturn( $release );
        $this->updater->shouldReceive( 'getLatestReleaseFromGitHub' )->with( 'my-token' )->andReturn( $this->release );

        $this->github->shouldReceive( 'exists' )->once()->andReturnTrue();
        $this->github->shouldReceive( 'get' )->once()->andReturn( '{ "github-oauth": { "github.com": "my-token" } }' );

        $command = $this->app->make( UpdateCheck::class );
        $tester  = $this->runCommand( $command, [
            '--force' => true,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

}
