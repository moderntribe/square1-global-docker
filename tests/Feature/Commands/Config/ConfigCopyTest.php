<?php

namespace Tests\Feature\Commands\Config;

use App\Commands\Config\ConfigCopy;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Commands\BaseCommandTester;

class ConfigCopyTest extends BaseCommandTester {

    public function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests' );
    }

    public function test_it_downloads_squareone_yml() {
        $command = $this->app->make( ConfigCopy::class, [
            'configDir'   => storage_path( 'tests' ),
            'downloadUrl' => 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/config/squareone.yml',
        ] );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Fetching config file...', $tester->getDisplay() );
        $this->assertStringContainsString( sprintf( 'Saved squareone.yml to %s', storage_path( 'tests' ) ), $tester->getDisplay() );

        $this->assertFileExists( storage_path( 'tests/squareone.yml' ) );

        $squareOneContent = file_get_contents( storage_path( 'tests/squareone.yml' ) );

        // Assert a number of strings that are likely to never change
        $this->assertStringContainsString( 'config-dir:', $squareOneContent );
        $this->assertStringContainsString( 'docker:', $squareOneContent );
    }

}
