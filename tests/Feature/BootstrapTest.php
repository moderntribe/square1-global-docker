<?php

namespace Tests\Feature;

use App\Bootstrap;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class BootstrapTest extends TestCase {

    private $filesystem;

    public function setUp(): void {
        parent::setUp();

        $this->filesystem = new \Illuminate\Filesystem\Filesystem();
    }

    public function test_it_copies_global_directory() {
        $configDir = storage_path( 'tests' );

        $bootstrap = new Bootstrap( $configDir, new Filesystem() );

        $bootstrap->boot();

        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/docker-compose.yml' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/certs' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/certs/README.md' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/mysql' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/mysql/mysql.cnf' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/nginx' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/nginx/nginx.conf' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/nginx/nginx.tmpl' ) ) );
        $this->assertTrue( $this->filesystem->exists( storage_path( 'tests/global/nginx/proxy_settings.conf' ) ) );

        Storage::disk( 'local' )->deleteDirectory( 'tests' );
    }
}
