<?php declare( strict_types=1 );

namespace Tests\Unit\Services;

use App\Commands\DockerCompose;
use App\Contracts\ArgumentRewriter;
use App\Services\ComposerVersion;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;
use phpmock\mockery\PHPMockery;
use Tests\TestCase;

final class ComposerVersionTest extends TestCase {

    /**
     * @var \App\Services\Docker\Local\Config
     */
    private $config;

    /**
     * @var \App\Services\ComposerVersion
     */
    private $composerVersion;

    /**
     * @var \App\Commands\DockerCompose
     */
    private $dockerCompose;

    protected function setUp(): void {
        parent::setUp();

        PHPMockery::mock( '\App\Services', 'chdir' )->andReturnTrue();

        $this->config          = $this->mock( Config::class );
        $this->composerVersion = new ComposerVersion();
        $this->dockerCompose   = $this->mock( DockerCompose::class );
    }

    public function test_it_detects_composer_v1() {
        $this->config->shouldReceive( 'getDockerDir' )
                     ->once()
                     ->andReturn( 'dev/docker' );

        $this->config->shouldReceive( 'getProjectName' )
                     ->once()
                     ->andReturn( 'square1' );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            'square1',
            'exec',
            '-T',
            'php-fpm',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->dockerCompose->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( 'Composer version 1.10.22 2021-04-27 13:10:45' );

        Artisan::swap( $this->dockerCompose );

        $this->assertTrue( $this->composerVersion->isVersionOne( $this->config ) );
    }

    public function test_it_detects_composer_v2() {
        $this->config->shouldReceive( 'getDockerDir' )
                     ->once()
                     ->andReturn( 'dev/docker' );

        $this->config->shouldReceive( 'getProjectName' )
                     ->once()
                     ->andReturn( 'square1' );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            'square1',
            'exec',
            '-T',
            'php-fpm',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->dockerCompose->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( 'Composer version 2.1.6 2021-08-19 17:11:08' );

        Artisan::swap( $this->dockerCompose );

        $this->assertFalse( $this->composerVersion->isVersionOne( $this->config ) );
    }

    /**
     * Future compatability
     */
    public function test_it_detects_composer_above_v2() {
        $this->config->shouldReceive( 'getDockerDir' )
                     ->once()
                     ->andReturn( 'dev/docker' );

        $this->config->shouldReceive( 'getProjectName' )
                     ->once()
                     ->andReturn( 'square1' );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            'square1',
            'exec',
            '-T',
            'php-fpm',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->dockerCompose->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( 'Composer version 3.0.0 2023-08-19 17:11:08' );

        Artisan::swap( $this->dockerCompose );

        $this->assertFalse( $this->composerVersion->isVersionOne( $this->config ) );
    }

    /**
     * Projects without composer, rare but exist.
     */
    public function test_it_detects_no_composer() {
        $this->config->shouldReceive( 'getDockerDir' )
                     ->once()
                     ->andReturn( 'dev/docker' );

        $this->config->shouldReceive( 'getProjectName' )
                     ->once()
                     ->andReturn( 'square1' );

        $this->dockerCompose->shouldReceive( 'call' )->with( DockerCompose::class, [
            '--project-name',
            'square1',
            'exec',
            '-T',
            'php-fpm',
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $this->dockerCompose->shouldReceive( 'output' )
                            ->once()
                            ->andReturn( '' );

        Artisan::swap( $this->dockerCompose );

        $this->assertFalse( $this->composerVersion->isVersionOne( $this->config ) );
    }

}
