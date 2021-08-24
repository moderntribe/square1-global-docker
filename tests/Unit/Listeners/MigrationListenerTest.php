<?php declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Listeners\MigrationListener;
use App\Services\Migrations\MigrationChecker;
use App\Services\Migrations\Migrator;
use App\Services\Update\Updater;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

final class MigrationListenerTest extends TestCase {

    private $finder;
    private $migrator;
    private $updater;
    private $event;
    private $checker;

    protected function setUp(): void {
        parent::setUp();

        // Force the listener to fire during tests.
        putenv( 'ALLOW_MIGRATION=1' );

        $this->finder   = $this->mock( Finder::class );
        $this->migrator = $this->mock( Migrator::class );
        $this->updater  = $this->mock( Updater::class );
        $this->event    = $this->mock( CommandStarting::class );
        $this->checker  = $this->mock( MigrationChecker::class );

        $this->event->command = 'global:start';
        $this->event->output  = new NullOutput();
    }

    public function test_it_runs_the_migrator() {
        // Force an update
        $this->checker->shouldReceive( 'shouldMigrate' )->once()->andReturnTrue();

        $this->finder->shouldReceive( 'files' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'name' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'in' )->once()->andReturnSelf();
        $this->finder->shouldReceive( 'count' )->once()->andReturn( 2 );

        $this->migrator->shouldReceive( 'run' )
                       ->once()
                       ->with( $this->finder, $this->event->output );

        $listener = new MigrationListener( $this->finder, $this->migrator, $this->checker, $this->updater, '1.0' );
        $result   = $listener->handle( $this->event );

        $this->assertTrue( $result );
    }

    public function test_it_does_not_run_migrator_with_specific_commands() {
        $listener = new MigrationListener( $this->finder, $this->migrator, $this->checker, $this->updater, '1.0' );

        $this->event->command = 'self:update';

        $result = $listener->handle( $this->event );

        $this->assertFalse( $result );

        $this->event->command = 'app:create-migration';

        $result = $listener->handle( $this->event );

        $this->assertFalse( $result );

    }

    public function test_it_does_not_run_migrator() {
        // Force disable migrations
        $this->checker->shouldReceive( 'shouldMigrate' )->once()->andReturnFalse();


        $listener = new MigrationListener( $this->finder, $this->migrator, $this->checker, $this->updater, '1.0' );
        $result   = $listener->handle( $this->event );

        $this->assertFalse( $result );
    }

}
