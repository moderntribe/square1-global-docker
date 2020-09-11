<?php

namespace Tests\Unit\Services;

use App\Runners\CommandRunner;
use App\Services\HomeDir;
use App\Services\ProjectBootstrapper;
use Illuminate\Filesystem\Filesystem;
use phpmock\mockery\PHPMockery;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

class ProjectBootstrapperTest extends TestCase {

    private $filesystem;
    private $homedir;
    private $runner;
    /**
     * @var ProjectBootstrapper
     */
    private $bootstrapper;
    private $projectRoot;

    protected function setUp(): void {
        parent::setUp();

        $this->filesystem   = $this->mock( Filesystem::class );
        $this->homedir      = $this->mock( HomeDir::class );
        $this->runner       = $this->mock( CommandRunner::class );
        $this->bootstrapper = $this->app->make( ProjectBootstrapper::class );
        $this->projectRoot  = storage_path( 'tests/project' );
    }

    public function test_it_renames_object_cache() {
        $objectCache = $this->projectRoot . '/wp-content/object-cache.php';

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $objectCache )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'move' )
                         ->once()
                         ->with( $objectCache, $this->projectRoot . '/wp-content/object-cache.bak.php' )
                         ->andReturnTrue();

        $this->bootstrapper->renameObjectCache( $this->projectRoot );
    }

    public function test_it_restores_object_cache() {
        $objectCache = $this->projectRoot . '/wp-content/object-cache.bak.php';

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $objectCache )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'move' )
                         ->once()
                         ->with( $objectCache, $this->projectRoot . '/wp-content/object-cache.php' )
                         ->andReturnTrue();

        $this->bootstrapper->restoreObjectCache( $this->projectRoot );
    }

    public function test_it_creates_databases() {
        $projectName = 'squareone';
        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'docker exec -i tribe-mysql mysql -uroot -ppassword <<< "CREATE DATABASE tribe_squareone;"' );

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'docker exec -i tribe-mysql mysql -uroot -ppassword <<< "CREATE DATABASE tribe_squareone_tests; CREATE DATABASE tribe_squareone_acceptance;"' );

        $this->bootstrapper->createDatabases( $projectName );
    }

    public function test_it_creates_local_config() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config.php' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'copy' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config-sample.php', $this->projectRoot . '/local-config.php' )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config-sample.php' )
                         ->andReturn( '//define( \'TRIBE_GLOMAR\', false );' );

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config.php', 'define( \'TRIBE_GLOMAR\', false );' )
                         ->andReturnTrue();

        $result = $this->bootstrapper->createLocalConfig( $this->projectRoot );

        $this->assertTrue( $result );
    }

    public function test_it_bypasses_existing_local_config() {
        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config.php' )
                         ->andReturnTrue();

        $result = $this->bootstrapper->createLocalConfig( $this->projectRoot );

        $this->assertFalse( $result );
    }

    /**
     * @runInSeparateProcess
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function test_it_creates_local_config_json_for_old_squareone() {
        PHPMockery::mock( 'App\Services', 'array_filter' )->andReturn( [
            $this->projectRoot . '/local-config-sample.json',
        ] );

        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config-sample.json' )
                         ->andReturn( 'square1.tribe "certs_path": ""');

        $this->homedir->shouldReceive( 'get' )->once()->andReturn( '/home/tests' );

        $this->filesystem->shouldReceive( 'missing' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config.json' )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( $this->projectRoot . '/local-config.json', 'squareone.tribe "certs_path": "/home/tests/.config/squareone/global/certs"' )
                         ->andReturnTrue();

        $this->bootstrapper->createLocalConfigJson( $this->projectRoot, 'squareone.tribe' );
    }

    /**
     * @runInSeparateProcess
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function test_it_does_not_create_local_config_json() {
        PHPMockery::mock( 'App\Services', 'array_filter' )->andReturn( [] );

        $this->bootstrapper->createLocalConfigJson( $this->projectRoot, 'squareone.tribe' );
    }


    /**
     * @runInSeparateProcess
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function test_it_creates_local_config_json_for_new_squareone() {
        PHPMockery::mock( 'App\Services', 'array_filter' )->andReturn( [
            $this->projectRoot . '/wp-content/themes/core/local-config-sample.json',
        ] );

        $this->filesystem->shouldReceive( 'get' )
                         ->once()
                         ->with( $this->projectRoot . '/wp-content/themes/core/local-config-sample.json' )
                         ->andReturn( 'square1.tribe "certs_path": ""');

        $this->homedir->shouldReceive( 'get' )->once()->andReturn( '/home/tests' );

        $this->filesystem->shouldReceive( 'missing' )
                         ->once()
                         ->with( $this->projectRoot . '/wp-content/themes/core/local-config.json' )
                         ->andReturnTrue();

        $this->filesystem->shouldReceive( 'put' )
                         ->once()
                         ->with( $this->projectRoot . '/wp-content/themes/core/local-config.json', 'squareone.tribe "certs_path": "/home/tests/.config/squareone/global/certs"' )
                         ->andReturnTrue();

        $this->bootstrapper->createLocalConfigJson( $this->projectRoot, 'squareone.tribe' );
    }

    public function test_it_builds_frontend_on_old_squareone_with_gulp() {

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/wp-content/themes/core/package.json' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/gulpfile.js' )
                         ->andReturnTrue();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( '. ~/.nvm/nvm.sh && nvm install && nvm use && yarn install && npm install -g gulp-cli && gulp dist' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();

        $this->bootstrapper->buildFrontend( $this->projectRoot, new NullOutput() );
    }

    public function test_it_builds_frontend_on_old_squareone_with_grunt() {

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/wp-content/themes/core/package.json' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/gulpfile.js' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/Gruntfile.js' )
                         ->andReturnTrue();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( '. ~/.nvm/nvm.sh && nvm install && nvm use && yarn install && npm install -g grunt-cli && grunt dist' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();

        $this->bootstrapper->buildFrontend( $this->projectRoot, new NullOutput() );
    }

    public function test_it_only_installs_on_old_squareone() {

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/wp-content/themes/core/package.json' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/gulpfile.js' )
                         ->andReturnFalse();

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/Gruntfile.js' )
                         ->andReturnFalse();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( '. ~/.nvm/nvm.sh && nvm install && nvm use && yarn install' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();

        $this->bootstrapper->buildFrontend( $this->projectRoot, new NullOutput() );
    }

    public function test_it_builds_frontend_on_new_squareone() {

        $this->filesystem->shouldReceive( 'exists' )
                         ->once()
                         ->with( $this->projectRoot . '/wp-content/themes/core/package.json' )
                         ->andReturnTrue();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( '. ~/.nvm/nvm.sh && nvm install && nvm use && npm install -g gulp-cli && npm run install:theme' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [
                         'path' => $this->projectRoot . '/wp-content/themes/core/',
                     ] )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( '. ~/.nvm/nvm.sh && nvm install && nvm use && npm run --prefix {{ $path }} gulp -- dist' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->twice()->andReturnSelf();

        $this->bootstrapper->buildFrontend( $this->projectRoot, new NullOutput() );
    }

}
