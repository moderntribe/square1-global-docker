<?php declare(strict_types=1);

namespace Tests\Feature\Commands\Self;

use RuntimeException;
use App\Services\Phar;
use Filebase\Document;
use App\Services\Update\Updater;
use App\Commands\Self\SelfUpdate;
use Tests\Feature\Commands\BaseCommandTester;

final class SelfUpdateTest extends BaseCommandTester {

    public function test_it_wont_run_withou_a_phar() {
        $this->expectException( RuntimeException::class );

        $command = $this->app->make( SelfUpdate::class );
        $this->runCommand( $command, [] );
    }

    public function test_runs_self_update_command() {
        $release          = $this->mock( Document::class );
        $release->version = '1.0.1';

        $updater = $this->mock( Updater::class );
        $updater->shouldReceive( 'getLatestReleaseFromGitHub' )->once()->andReturn( $release );
        $updater->shouldReceive( 'update' )->once();

        $phar = $this->mock( Phar::class );
        $phar->shouldReceive( 'isPhar' )->once()->andReturn( true );

        $command = $this->app->make( SelfUpdate::class );

        $tester = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_shows_error_when_no_releases_exist() {
        $updater = $this->mock( Updater::class );
        $updater->shouldReceive( 'getLatestReleaseFromGitHub' )->once()->andReturnNull();

        $phar = $this->mock( Phar::class );
        $phar->shouldReceive( 'isPhar' )->once()->andReturn( true );

        $command = $this->app->make( SelfUpdate::class );

        $tester = $this->runCommand( $command, [] );

        $this->assertSame( 1, $tester->getStatusCode() );
        $this->assertStringContainsString( 'No releases found! Aborting update.', $tester->getDisplay() );
    }
}
