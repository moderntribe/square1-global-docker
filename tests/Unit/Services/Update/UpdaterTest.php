<?php

namespace Tests\Unit\Services\Update;

use App\Commands\Self\SelfUpdate;
use App\Services\Update\Installer;
use App\Services\Update\Updater;
use Filebase\Database;
use Filebase\Document;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdaterTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
    }

    public function test_it_can_get_the_lastest_release_from_github() {
        Http::fake( [
            'github.com/*' => Http::response( [
                'tag_name' => '2.2.1-beta',
                'assets'   => [
                    [
                        'browser_download_url' => 'https://github.com/moderntribe/tribe-local/releases/download/2.2.1-beta/tribe.phar',
                    ],
                ],
            ], 200, [ 'Headers' ] ),
        ] );

        $this->mock( Document::class );
        $this->mock( Database::class );

        $installer = $this->mock( Installer::class );
        $installer->shouldReceive( 'download' )->once();

        $updater = $this->app->make( Updater::class );

        $release = $updater->getLatestReleaseFromGitHub();

        $this->assertInstanceOf( Document::class, $release );
        $this->assertEquals( '2.2.1-beta', $release->version );
        $this->assertEquals( 'https://github.com/moderntribe/tribe-local/releases/download/2.2.1-beta/tribe.phar', $release->download );

        Http::assertSentCount( 1 );

        Http::assertSent( function ( Request $request ): bool {
            return ( Updater::UPDATE_URL === $request->url() );
        } );

        $updater->update( $release, '/tmp/random.phar', $this->app->make( SelfUpdate::class ) );
    }

    public function test_it_can_get_a_release_with_a_github_token() {
        $token = 'githubtoken';

        Http::fake( [
            'github.com/*' => Http::response( [
                'tag_name' => '2.2.1-beta',
                'assets'   => [
                    [
                        'browser_download_url' => 'https://github.com/moderntribe/tribe-local/releases/download/2.2.1-beta/tribe.phar',
                    ],
                ],
            ], 200, [ 'Headers' ] ),
        ] );

        Http::withToken( $token );

        $this->mock( Document::class );
        $this->mock( Database::class );

        $installer = $this->mock( Installer::class );
        $installer->shouldReceive( 'download' )->once();

        $updater = $this->app->make( Updater::class );

        $release = $updater->getLatestReleaseFromGitHub( $token );

        $this->assertInstanceOf( Document::class, $release );
        $this->assertEquals( '2.2.1-beta', $release->version );
        $this->assertEquals( 'https://github.com/moderntribe/tribe-local/releases/download/2.2.1-beta/tribe.phar', $release->download );

        Http::assertSentCount( 1 );

        Http::assertSent( function ( Request $request ) use ( $token ): bool {
            return ( Updater::UPDATE_URL === $request->url() );
        } );

        $updater->update( $release, '/tmp/random.phar', $this->app->make( SelfUpdate::class ) );
    }

    public function test_it_handles_a_missing_release() {
        Http::fake( [
            'github.com/*' => Http::response( [], 404, [ 'Headers' ] ),
        ] );

        $updater = $this->app->make( Updater::class );

        $release = $updater->getLatestReleaseFromGitHub();

        $this->assertNull( $release );

        Http::assertSentCount( 1 );
    }

    public function test_it_handles_a_missing_tag_name() {
        Http::fake( [
            'github.com/*' => Http::response( [
                'assets'   => [
                    [
                        'browser_download_url' => 'https://github.com/moderntribe/tribe-local/releases/download/2.2.1-beta/tribe.phar',
                    ],
                ],
            ], 200, [ 'Headers' ] ),
        ] );

        $updater = $this->app->make( Updater::class );

        $release = $updater->getLatestReleaseFromGitHub();

        $this->assertNull( $release );

        Http::assertSentCount( 1 );
    }

}
