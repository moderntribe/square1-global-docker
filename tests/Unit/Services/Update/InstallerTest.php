<?php

namespace Tests\Unit\Services\Update;

use Exception;
use App\Services\Phar;
use Filebase\Document;
use App\Services\Update\Installer;
use App\Exceptions\SystemExitException;
use LaravelZero\Framework\Components\Updater\SelfUpdateCommand;
use Symfony\Component\Filesystem\Filesystem;
use Tests\TestCase;

class InstallerTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
    }

    public function test_it_downloads_a_release() {
        $this->expectException( SystemExitException::class );
        $this->expectExceptionMessage( 'Upgrade complete' );

        $file     = storage_path( 'tests/so.phar' );
        $tempFile = storage_path( 'tests/tmp/so_rand.phar' );

        $filesystem = $this->mock( Filesystem::class );
        $filesystem->shouldReceive( 'tempnam' )->with( '/tmp', 'so_', '.phar' )->once()->andReturn( $tempFile );
        $filesystem->shouldReceive( 'copy' )->once();
        $filesystem->shouldReceive( 'chmod' )->with( $tempFile, 0755 )->once();
        $filesystem->shouldReceive( 'rename' )->with( $tempFile, $file, true )->once();

        $release           = $this->mock( Document::class );
        $release->download = 'https://github.com/path/to/release.phar';
        $release->version  = '5.0.0';

        $phar = $this->mock( Phar::class );
        $phar->shouldReceive( 'testPhar' )->with( $tempFile )->once();

        $command = $this->mock( SelfUpdateCommand::class );
        $command->shouldReceive( 'info' )->once();

        $installer = $this->app->make( Installer::class );

        $installer->download( $release, $file, $command );
    }

    public function test_it_fails_on_a_bad_phar() {
        $this->expectException( Exception::class );
        $this->expectExceptionMessage( 'Cannot create phar' );

        $file     = storage_path( 'tests/so.phar' );
        $tempFile = storage_path( 'tests/tmp/so_rand.phar' );

        $filesystem = $this->mock( Filesystem::class );
        $filesystem->shouldReceive( 'tempnam' )->with( '/tmp', 'so_', '.phar' )->once()->andReturn( $tempFile );
        $filesystem->shouldReceive( 'copy' )->once();
        $filesystem->shouldReceive( 'chmod' )->with( $tempFile, 0755 )->once();
        $filesystem->shouldReceive( 'remove' )->with( [ 0 => $tempFile ] )->once();

        $release           = $this->mock( Document::class );
        $release->download = 'https://github.com/path/to/release.phar';
        $release->version  = '5.0.0';

        $command = $this->mock( SelfUpdateCommand::class );

        $installer = $this->app->make( Installer::class );

        $installer->download( $release, $file, $command );
    }

}
