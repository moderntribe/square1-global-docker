<?php

namespace Tests\Feature\Services\Migrations;

use App\Services\Migrations\MigrationCreator;
use App\Services\Migrations\Migrator;
use Filebase\Database;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Class MigrationTest
 *
 * @runTestsInSeparateProcesses
 *
 * @package Tests\Feature\Services\Migrations
 */
class MigrationTest extends TestCase {

    protected $filesystem;
    protected $creator;
    protected $migrations;
    protected $db;
    protected $migrator;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests/migrations' );

        $this->filesystem = new Filesystem();
        $this->creator = $this->app->make( MigrationCreator::class );

        $this->migrations = $this->getMigrations();

        $this->db = new Database( [
            'dir' => storage_path( 'tests/store/migrations' ),
        ] );

        $this->migrator = new Migrator( $this->db, $this->filesystem, $this->app );
    }

    protected function getMigrations(): Finder {
        $migration1 = $this->creator->getMigrationData( 'test_migration', storage_path( 'tests/migrations' ) );

        $this->filesystem->put( $migration1->path, $migration1->content );

        $migration2 = $this->creator->getMigrationData( 'test_migration_two', storage_path( 'tests/migrations' ) );

        $this->filesystem->put( $migration2->path, $migration2->content );

        $finder = new Finder();

        $finder->files()->name( '*.php' )->in( storage_path( 'tests/migrations' ) );

        return $finder;
    }

    public function test_it_fails_to_run_a_bad_migration() {
        $name = 'bad_migration';
        $path = storage_path( 'tests/migrations' );

        $migration = $this->creator->getMigrationData( $name, $path );

        $content = str_replace( 'return true', 'return false', $migration->content );

        $this->filesystem->put( $migration->path, $content );

        $finder = new Finder();
        $finder->files()->name( '*.php' )->in( storage_path( 'tests/migrations' ) );

        $results = $this->migrator->run( $finder, new NullOutput() );

        $this->assertCount( 2, $results->where( 'success', true ) );
    }

    public function test_it_runs_multiple_migrations() {
        $results = $this->migrator->run( $this->migrations, new NullOutput() );

        $this->assertCount( 2, $results );

        $migrations = iterator_to_array( $this->migrations, false );

        // Assert order of migrations
        $this->assertTrue( $results[0]->success );
        $this->assertEquals( $this->filesystem->basename( $migrations[0]->getPathName() ), $results[0]->migration );

        // Assert order of migrations
        $this->assertTrue( $results[1]->success );
        $this->assertEquals( $this->filesystem->basename( $migrations[1]->getPathName() ), $results[1]->migration );

        // Assert data store was created
        $migration_1 = storage_path( 'tests/store/migrations/' ) . $this->filesystem->name( $migrations[0]->getPathName() ) . '.json';
        $this->assertFileExists( $migration_1 );
        $this->assertStringContainsString( '__created_at', $this->filesystem->get( $migration_1 ) );

        $migration_2 = storage_path( 'tests/store/migrations/' ) . $this->filesystem->name( $migrations[1]->getPathName() ) . '.json';
        $this->assertFileExists( $migration_2 );
        $this->assertStringContainsString( '__created_at', $this->filesystem->get( $migration_2 ) );

        // Assert it doesn't run migrations again
        $results_2 = $this->migrator->run( $this->migrations, new NullOutput() );

        $this->assertCount( 0, $results_2 );
    }

}
