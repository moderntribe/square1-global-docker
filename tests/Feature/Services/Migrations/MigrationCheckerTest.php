<?php declare(strict_types=1);

namespace Tests\Feature\Services\Migrations;

use App\Services\Migrations\MigrationChecker;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Filesystem\Filesystem;
use Tests\TestCase;

/**
 * Migration Checker Test
 *
 * @runTestsInSeparateProcesses
 */
final class MigrationCheckerTest extends TestCase {

    private $checker;
    private $filesystem;
    private $migratedFile;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests/migration-checker' );

        $this->filesystem   = new Filesystem();
        $this->checker      = new MigrationChecker( $this->filesystem, storage_path( 'tests/migration-checker' ) );
        $this->migratedFile = storage_path( sprintf( 'tests/migration-checker/%s', MigrationChecker::MIGRATION_FILE ) );
    }

    protected function tearDown(): void {
        parent::tearDown();

        $this->checker->clear();
    }

    public function test_it_would_run_migrations() {
        $this->checker->clear();

        $this->assertFileDoesNotExist( $this->migratedFile );

        $this->assertTrue( $this->checker->shouldMigrate() );
    }

    public function test_it_would_not_run_migrations() {
        $this->checker->update();

        $this->assertFileExists( $this->migratedFile );

        $this->assertFalse( $this->checker->shouldMigrate() );
    }

}
