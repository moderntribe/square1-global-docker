<?php declare( strict_types=1 );

namespace Tests\Unit\Services;

use App\Commands\Docker;
use App\Contracts\ArgumentRewriter;
use App\Services\ComposerVersion;
use App\Services\Docker\Container;
use Illuminate\Support\Facades\Artisan;
use phpmock\mockery\PHPMockery;
use Tests\TestCase;

final class ComposerVersionTest extends TestCase {

    /**
     * @var \App\Services\Docker\Container
     */
    private $container;

    /**
     * @var \App\Services\ComposerVersion
     */
    private $composerVersion;

    /**
     * @var \App\Commands\Docker
     */
    private $docker;

    protected function setUp(): void {
        parent::setUp();

        PHPMockery::mock( '\App\Services', 'chdir' )->andReturnTrue();

        $this->container       = $this->mock( Container::class );
        $this->composerVersion = new ComposerVersion( $this->container );
        $this->docker          = $this->mock( Docker::class );
    }

    public function test_it_detects_composer_v1() {
        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            'php-fpm-container-id',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->docker->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( 'Composer version 1.10.22 2021-04-27 13:10:45' );

        Artisan::swap( $this->docker );

        $this->assertTrue( $this->composerVersion->isVersionOne() );
    }

    public function test_it_detects_composer_v2() {
        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            'php-fpm-container-id',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->docker->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( 'Composer version 2.1.6 2021-08-19 17:11:08' );

        Artisan::swap( $this->docker );

        $this->assertFalse( $this->composerVersion->isVersionOne() );
    }

    /**
     * Future compatability
     */
    public function test_it_detects_composer_above_v2() {
        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            'php-fpm-container-id',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->docker->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( 'Composer version 3.0.0 2023-08-19 17:11:08' );

        Artisan::swap( $this->docker );

        $this->assertFalse( $this->composerVersion->isVersionOne() );
    }

    /**
     * Projects without composer, rare but exist.
     */
    public function test_it_detects_no_composer() {
        $this->container->shouldReceive( 'getId' )
                        ->once()
                        ->andReturn( 'php-fpm-container-id' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            'php-fpm-container-id',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->docker->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( '' );

        Artisan::swap( $this->docker );

        $this->assertFalse( $this->composerVersion->isVersionOne() );
    }

}
