<?php

namespace Tests\Feature\Commands\App;

use stdClass;
use App\Commands\App\CreateMigration;
use App\Services\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Commands\BaseCommandTest;

class CreateMigrationTest extends BaseCommandTest {

    private $creator;
    private $filesystem;

    public function setUp(): void {
        parent::setUp();

        Storage::fake();

        $this->creator    = $this->mock( MigrationCreator::class );
        $this->filesystem = $this->mock( Filesystem::class );

    }

    public function test_it_runs_migration_creator() {
        $migration          = new stdClass();
        $migration->path    = storage_path( 'tests/migrations/test_migration.php' );
        $migration->content = 'temp content';

        $this->creator->shouldReceive( 'getMigrationData' )
                      ->once()
                      ->with( 'test', storage_path( 'migrations' ) )
                      ->andReturn( $migration );

        $this->filesystem->shouldReceive( 'put' )->once()->with( $migration->path, $migration->content );

        $command = $this->app->make( CreateMigration::class );
        $tester  = $this->runCommand( $command, [], [
            'Enter the name of this migration' => 'test',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Migration file created at ' . $migration->path, $tester->getDisplay() );
    }

}
