<?php

namespace Tests\Feature\Services\Migrations;

use Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use App\Services\Migrations\MigrationCreator;


class MigrationCreatorTest extends TestCase {

    protected function tearDown(): void {
        Storage::deleteDirectory( 'tests' );

        parent::tearDown();
    }

    public function test_it_creates_migration_from_stub() {
        $creator = new MigrationCreator( new Filesystem() );

        $name = 'test_stub_migration';
        $path = storage_path( 'tests/migrations' );

        $data = $creator->getMigrationData( $name, $path );

        $this->assertStringContainsString( '_test_stub_migration', $data->path );
        $this->assertStringContainsString( 'final class TestStubMigration', $data->content );

        $classFile = $creator->populateStub( $name, $creator->getStub() );

        $this->assertEquals( $classFile, $data->content );

    }
}
