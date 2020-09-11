<?php

namespace Tests\Unit\Services\Config;

use Tests\TestCase;
use App\Services\Config\Env;
use Illuminate\Filesystem\Filesystem;

class EnvTest extends TestCase {

    private $filesystem;
    private $env;

    protected function setUp(): void {
        parent::setUp();

        $this->filesystem = $this->mock( Filesystem::class );
        $this->env        = new Env( $this->filesystem, storage_path( 'tests' ) );
    }

    public function test_it_gets_env_vars() {
        $this->filesystem->shouldReceive( 'get' )->once()->with( storage_path( 'tests/defaults/.env' ) );
        $this->env->getVars();
    }

    public function test_it_finds_env_file() {
        $this->filesystem->shouldReceive( 'exists' )->once()->with( storage_path( 'tests/defaults/.env' ) )->andReturnTrue();
        $this->assertTrue( $this->env->exists() );
    }

    public function test_it_copies_env_file() {
        $this->filesystem->shouldReceive( 'copy' )->once()->with( storage_path( 'tests/defaults/.env' ), storage_path( 'tests/project/.env' ) )->andReturnTrue();
        $this->assertTrue( $this->env->copy( storage_path( 'tests/project' ) ) );
    }

    public function test_it_saves_env_file() {
        $content = 'test';
        $this->filesystem->shouldReceive( 'put' )->once()->with(  storage_path( 'tests/defaults/.env' ), $content )->andReturnTrue();
        $this->assertTrue( $this->env->save( $content ) );
    }

    public function test_it_diffs_env_file() {
        $this->filesystem->shouldReceive( 'get' )->once()->with( storage_path( 'tests/project/.env.sample' ) )->andReturn( $this->getSampleEnv() );
        $this->filesystem->shouldReceive( 'get' )->once()->with( storage_path( 'tests/defaults/.env' ) )->andReturn( $this->getDefaultEnv() );

        $diff = $this->env->diff( storage_path( 'tests/project/.env.sample' ) );

        $this->assertArrayHasKey( 'WP_RANDOM_KEY', $diff );
    }

    private function getSampleEnv(): string {
        return "WP_PLUGIN_ACF_KEY=''
                WP_PLUGIN_GF_KEY=''
                WP_RANDOM_KEY=''
                WP_PLUGIN_GF_TOKEN=''";
    }

    private function getDefaultEnv(): string {
        return "WP_PLUGIN_ACF_KEY=''
                WP_PLUGIN_GF_KEY=''
                WP_PLUGIN_GF_TOKEN=''";
    }

}
