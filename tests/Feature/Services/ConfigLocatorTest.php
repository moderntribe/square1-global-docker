<?php declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Services\ConfigLocator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ConfigLocatorTest extends TestCase {

    public function test_it_finds_a_config_file_in_the_project_root(): void {
        Storage::disk( 'local' )->makeDirectory( 'tests/fakeproject' );
        Storage::disk( 'local' )->put( 'tests/fakeproject/squareone.yml', '' );

        $locator = new ConfigLocator();

        $file = $locator->find( storage_path( 'tests/fakeproject' ) );

        $this->assertSame( storage_path( 'tests/fakeproject/squareone.yml' ), $file );
    }

    public function test_it_finds_a_custom_config_file_in_the_project_root(): void {
        Storage::disk( 'local' )->makeDirectory( 'tests/fakeproject' );
        Storage::disk( 'local' )->put( 'tests/fakeproject/customconfig.yml', '' );

        $locator = new ConfigLocator();

        $file = $locator->find( storage_path( 'tests/fakeproject' ), 'customconfig.yml' );

        $this->assertSame( storage_path( 'tests/fakeproject/customconfig.yml' ), $file );
    }

    public function test_it_finds_a_config_file_from_project_sub_folders(): void {
        Storage::disk( 'local' )->makeDirectory( 'tests/newfakeproject/some/sub/folder' );
        Storage::disk( 'local' )->put( 'tests/newfakeproject/squareone.yml', '' );

        $locator = new ConfigLocator();

        $file = $locator->find( storage_path( 'tests/newfakeproject/some/sub/folder' ) );

        $this->assertSame( storage_path( 'tests/newfakeproject/squareone.yml' ), $file );
    }

    public function test_it_finds_a_custom_config_file_from_project_sub_folders(): void {
        Storage::disk( 'local' )->makeDirectory( 'tests/fakeproject/some/sub/folder' );
        Storage::disk( 'local' )->put( 'tests/fakeproject/localconfig.yml', '' );

        $locator = new ConfigLocator();

        $file = $locator->find( storage_path( 'tests/fakeproject/some/sub/folder' ), 'localconfig.yml' );

        $this->assertSame( storage_path( 'tests/fakeproject/localconfig.yml' ), $file );
    }

    public function test_it_does_not_find_a_config_file(): void {
        $locator = new ConfigLocator();

        $file = $locator->find( '/tmp' );

        $this->assertSame( '', $file );
    }

}
