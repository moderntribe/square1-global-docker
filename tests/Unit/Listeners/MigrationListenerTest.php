<?php

namespace Tests\Unit\Listeners;

use Tests\TestCase;
use Filebase\Database;
use Filebase\Document;
use App\Services\Update\Updater;
use Symfony\Component\Finder\Finder;
use App\Listeners\MigrationListener;
use App\Services\Migrations\Migrator;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Output\NullOutput;

class MigrationListenerTest extends TestCase {

    private $finder;
    private $migrator;
    private $updater;
    private $event;
    private $db;

    public function setUp(): void {
        parent::setUp();

        // Force the listener to fire during tests.
        putenv( 'ALLOW_MIGRATION=1' );

        $this->finder   = $this->mock( Finder::class );
        $this->migrator = $this->mock( Migrator::class );
        $this->updater  = $this->mock( Updater::class );
        $this->event    = $this->mock( CommandStarting::class );
        $this->db       = $this->mock( Database::class );

        $this->event->command = 'global:start';
        $this->event->output  = new NullOutput();
    }

    public function test_it_runs_the_migrator() {
        // Return empty release, so migrations should run
        $this->updater->shouldReceive( 'getCachedRelease' )
                      ->once()
                      ->andReturn( new Document( $this->db ) );

        $this->finder->shouldReceive( 'files' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'name' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'in' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'getIterator' )
                     ->once()
                     ->andReturn( new \ArrayIterator( [
                         'mock migration file found 1',
                         'mock migration file found 2',
                     ] ) );

        $this->migrator->shouldReceive( 'run' )
                       ->once()
                       ->with( $this->finder, $this->event->output );

        $listener = new MigrationListener( $this->finder, $this->migrator, $this->updater, '1.0' );
        $result   = $listener->handle( $this->event );

        $this->assertTrue( $result );
    }

    public function test_it_does_not_run_migrator_with_specific_commands() {
        $this->event->command = null;

        $listener = new MigrationListener( $this->finder, $this->migrator, $this->updater, '1.0' );

        $result = $listener->handle( $this->event );

        $this->assertFalse( $result );

        $this->event->command = 'self:update';

        $result = $listener->handle( $this->event );

        $this->assertFalse( $result );

        $this->event->command = 'app:create-migration';

        $result = $listener->handle( $this->event );

        $this->assertFalse( $result );

    }

    public function test_it_runs_migrator_with_proper_version() {
        $release = new Document( $this->db );
        // Version is below the current running version
        $release->version = '0.5';

        $this->updater->shouldReceive( 'getCachedRelease' )
                      ->once()
                      ->andReturn( $release );

        $this->finder->shouldReceive( 'files' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'name' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'in' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'getIterator' )
                     ->once()
                     ->andReturn( new \ArrayIterator( [
                         'mock migration file found 1',
                         'mock migration file found 2',
                     ] ) );

        $this->migrator->shouldReceive( 'run' )
                       ->once()
                       ->with( $this->finder, $this->event->output );

        $listener = new MigrationListener( $this->finder, $this->migrator, $this->updater, '1.0' );
        $result   = $listener->handle( $this->event );

        $this->assertTrue( $result );
    }

    public function test_it_does_not_run_migrator_if_newer_version() {
        $release = new Document( $this->db );
        // Version is above the passed current running version
        $release->version = '5.0.0';

        $this->updater->shouldReceive( 'getCachedRelease' )
                      ->once()
                      ->andReturn( $release );

        $listener = new MigrationListener( $this->finder, $this->migrator, $this->updater, '1.0' );
        $result   = $listener->handle( $this->event );

        $this->assertFalse( $result );
    }

}
