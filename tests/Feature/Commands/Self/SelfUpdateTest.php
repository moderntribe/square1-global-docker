<?php

namespace Tests\Feature\Commands\Self;

use RuntimeException;
use App\Services\Phar;
use Filebase\Document;
use App\Commands\Self\SelfUpdate;
use App\Services\Update\Updater;
use Tests\Feature\Commands\BaseCommandTest;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SelfUpdateTest extends BaseCommandTest {

    public function testItWontRunWithoutaPhar() {
        $this->expectException( RuntimeException::class );

        $command = $this->app->make( SelfUpdate::class );
        $this->runCommand( $command, [] );
    }

    public function testItWillRunSelfUpdateCommand() {
        $release          = $this->mock( Document::class );
        $release->version = '1.0.1';

        $this->mock( Updater::class, function ( $mock ) use ( $release ) {
            $mock->shouldReceive( 'getLatestReleaseFromGitHub' )->once()->andReturn( $release );
            $mock->shouldReceive( 'update' )->once();
        } );

        $this->mock( Phar::class, function ( $mock ) {
            $mock->shouldReceive( 'isPhar' )->once()->andReturn( true );
        } );

        $command = $this->app->make( SelfUpdate::class );

        $tester = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }
}
