<?php

namespace Tests\Feature\Commands\Config;

use App\Commands\Config\ComposeCopy;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Commands\BaseCommandTester;

class ComposeCopyTest extends BaseCommandTester {

    public function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests/global' );
    }

    public function test_it_downloads_docker_compose_yml() {
        $command = $this->app->make( ComposeCopy::class, [
            'composeOverride' => storage_path( 'tests/global/docker-compose.override.yml' ),
            'downloadUrl'     => 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/global/docker-compose.yml',
        ] );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Fetching docker-compose.yml...', $tester->getDisplay() );
        $this->assertStringContainsString( 'Saved to', $tester->getDisplay() );
        $this->assertStringContainsString( 'tests/global/docker-compose.override.yml', $tester->getDisplay() );

        $this->assertFileExists( storage_path( 'tests/global/docker-compose.override.yml' ) );

        $dockerComposeContent = file_get_contents( storage_path( 'tests/global/docker-compose.override.yml' ) );

        // Assert a number of strings that are likely to never change
        $this->assertStringContainsString( 'version:', $dockerComposeContent );
        $this->assertStringContainsString( 'dns-external:', $dockerComposeContent );
        $this->assertStringContainsString( 'proxy:', $dockerComposeContent );
    }

}
