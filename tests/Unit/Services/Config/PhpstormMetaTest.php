<?php declare(strict_types=1);

namespace Tests\Unit\Services\Config;

use App\Services\Config\PhpStormMeta;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

final class PhpstormMetaTest extends TestCase {

    /**
     * @var \Mockery\MockInterface|Filesystem
     */
    private $filesystem;

    /**
     * @var \App\Services\Config\PhpStormMeta
     */
    private $phpStormMeta;

    protected function setUp(): void {
        parent::setUp();

        $this->filesystem   = $this->mock( Filesystem::class );
        $this->phpStormMeta = new PhpStormMeta( $this->filesystem, storage_path( 'tests' ) );
    }

    public function test_it_finds_existing_meta_in_project(): void {
        $this->filesystem->shouldReceive( 'exists' )
            ->once()
            ->with( sprintf( '/tmp/local-project%s', PhpStormMeta::META_FILE ) )
            ->andReturnTrue();

        $this->assertTrue( $this->phpStormMeta->existsInProject( '/tmp/local-project' ) );
    }

    public function test_it_copies_phpstorm_meta_file(): void {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( sprintf( '/tmp/local-project%s', PhpStormMeta::META_FILE ) )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( storage_path( sprintf( 'tests/defaults%s', PhpStormMeta::PHAR_META_FILE ) ) )
                         ->andReturn( 'mock .phpstorm.meta.php content' );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( sprintf( '/tmp/local-project%s', PhpStormMeta::META_FILE ), 'mock .phpstorm.meta.php content' )
                         ->andReturnTrue();

        $this->assertFalse( $this->phpStormMeta->existsInProject( '/tmp/local-project' ) );
        $this->assertTrue( $this->phpStormMeta->copy( '/tmp/local-project' ) );

    }

}
