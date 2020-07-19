<?php

namespace Tests\Unit\Services\Config;

use Tests\TestCase;
use App\Services\Config\Github;
use Illuminate\Filesystem\Filesystem;


class GithubTest extends TestCase {

    private $filesystem;
    private $github;

    public function setUp(): void {
        parent::setUp();

        $this->filesystem = $this->mock( Filesystem::class );
        $this->github     = new Github( $this->filesystem, storage_path( 'tests' ) );
    }

    public function test_it_gets_auth_file_contents() {
        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( storage_path( 'tests/defaults/auth.json' ) )
                         ->andReturn( 'a string' );
        $this->assertSame( 'a string', $this->github->get() );
    }

    public function test_auth_file_exists() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( storage_path( 'tests/defaults/auth.json' ) )
                         ->andReturnTrue();
        $this->assertTrue( $this->github->exists() );
    }

    public function test_auth_file_saves() {
        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( storage_path( 'tests/defaults/auth.json' ), '{ "github-oauth": { "github.com": "my-token" } }')
                         ->andReturnTrue();
        $this->assertTrue( $this->github->save( 'my-token' ) );
    }

    public function test_it_copies_auth_file() {
        $this->filesystem->shouldReceive( 'copy' )
                         ->once()
                         ->with( storage_path( 'tests/defaults/auth.json' ), storage_path( 'tests/composer/auth.json' ) )
                         ->andReturnTrue();
        $this->assertTrue( $this->github->copy( storage_path( 'tests/composer' ) ) );
    }

}
