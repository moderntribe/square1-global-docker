<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FileIO;
use Illuminate\Filesystem\Filesystem;

class FileIOTest extends TestCase {

    private $filesystem;

    protected function setUp(): void {
        parent::setUp();

        $this->filesystem = $this->mock( Filesystem::class );
    }

    public function test_file_exists() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';

        $this->filesystem->shouldReceive( 'exists' )->with( $path )->once()->andReturnTrue();

        $result = $file->exists( $path );

        $this->assertTrue( $result );
    }

    public function test_file_does_not_exist() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';

        $this->filesystem->shouldReceive( 'exists' )->with( $path )->once()->andReturnFalse();

        $result = $file->exists( $path );

        $this->assertFalse( $result );
    }

    public function test_it_can_get_file_contents() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';

        $this->filesystem->shouldReceive( 'get' )->with( $path )->once()->andReturn( 'my content' );

        $result = $file->get( $path );

        $this->assertSame( 'my content', $result );
    }

    public function test_it_finds_string_in_file() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';

        $this->filesystem->shouldReceive( 'get' )->with( $path )->once()->andReturn( 'This is test Content. Find Me, Okay?' );

        $result = $file->contains( $path, 'Find Me' );

        $this->assertTrue( $result );
    }

    public function test_it_does_not_find_string_in_file() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';

        $this->filesystem->shouldReceive( 'get' )->with( $path )->once()->andReturn( 'This is test Content. Find Me, Okay?' );

        $result = $file->contains( $path, 'Search for missing text' );

        $this->assertFalse( $result );
    }

    public function test_it_appends_content_to_file() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';
        $content = 'Added to end of file';

        $this->filesystem->shouldReceive( 'append' )->with( $path, $content )->once()->andReturn( 20 );

        $result = $file->append_content( $path, $content );

        $this->assertSame( 20, $result );
    }

    public function test_it_removes_content_from_file() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';
        $content = 'Remove me.';

        $this->filesystem->shouldReceive( 'get' )->with( $path )->once()->andReturn( 'This is test content. Remove me. This is test content.' );
        $this->filesystem->shouldReceive( 'replace' )->with( $path, 'This is test content.  This is test content.' )->once();

        $file->remove_content( $path, $content );
    }

    public function test_it_replaces_content_in_file() {
        $file = new FileIO( $this->filesystem );
        $path = '/tmp/test.txt';

        $this->filesystem->shouldReceive( 'get' )->with( $path )->once()->andReturn( 'Hello, world!' );
        $this->filesystem->shouldReceive( 'replace' )->with( $path, 'Hello, universe!' )->once();

        $file->replace_content( $path, 'world', 'universe' );
    }
}
