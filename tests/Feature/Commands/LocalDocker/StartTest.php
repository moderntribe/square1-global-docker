<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\Open;
use App\Services\Config\Env;
use App\Runners\CommandRunner;
use App\Commands\DockerCompose;
use App\Services\Config\Github;
use App\Commands\LocalDocker\Start;
use Illuminate\Console\OutputStyle;
use App\Services\Certificate\Handler;
use App\Services\Docker\Local\Config;
use Illuminate\Filesystem\Filesystem;
use App\Commands\LocalDocker\Composer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Commands\BaseCommandTester;
use App\Commands\GlobalDocker\Start as GlobalStart;

class StartTest extends BaseCommandTester {

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests/dev/docker' );
    }

    public function test_it_can_start_a_project_with_standard_default_env_file() {
        $config = $this->mock( Config::class );
        $config->shouldReceive( 'getComposerVolume' )
               ->twice()
               ->andReturn( storage_path( 'tests/dev/docker/composer' ) );

        $config->shouldReceive( 'getProjectName' )->times( 4 )->andReturn( 'squareone' );
        $config->shouldReceive( 'getProjectRoot' )->times( 5 )->andReturn( storage_path( 'tests' ) );
        $config->shouldReceive( 'getDockerDir' )->twice()->andReturn( storage_path( 'tests/dev/docker' ) );
        $config->shouldReceive( 'getProjectUrl' )->once()->andReturn( 'https://squareone.tribe' );
        $config->shouldReceive( 'setPath' )
               ->once()
               ->with( storage_path( 'tests' ) )
               ->andReturnSelf();

        $certHandler = $this->mock( Handler::class );
        $certHandler->shouldReceive( 'caExists' )->once()->andReturnTrue();
        $certHandler->shouldReceive( 'createCertificate' )->once();

        $env = $this->mock( Env::class );

        $env->shouldReceive( 'exists' )
            ->once()
            ->andReturnFalse();

        $env->shouldReceive( 'save' )
            ->once()
            ->with( $this->getDefaultEnv() );

        // No difference between env files
        $env->shouldReceive( 'diff' )
            ->once()
            ->with( storage_path( 'tests/.env.sample' ) )
            ->andReturn( [] );

        $env->shouldReceive( 'copy' )
            ->once()
            ->with( storage_path( 'tests' ) )
            ->andReturnTrue();

        $github = $this->mock( Github::class );

        $github->shouldReceive( 'exists' )
               ->once()
               ->andReturnTrue();

        $github->shouldReceive( 'copy' )
               ->once()
               ->with( storage_path( 'tests/dev/docker/composer' ) )
               ->andReturnTrue();

        $filesystem = $this->mock( Filesystem::class );

        $filesystem->shouldReceive( 'get' )
                   ->once()
                   ->with( storage_path( 'defaults/env' ) )
                   ->andReturn( $this->getDefaultEnv() );

        $filesystem->shouldReceive( 'exists' )
                   ->once()
                   ->with( storage_path( 'tests/.env.sample' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/.env' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/dev/docker/composer/auth.json' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/dev/docker/composer/composer.lock' ) )
                   ->andReturnTrue();


        $runner = $this->mock( CommandRunner::class );
        $runner->shouldReceive( 'throw' )->once()->andReturn( $runner );

        // Assert vm time sync runs.
        $runner->shouldReceive( 'run' )
               ->with( 'docker run --privileged --rm phpdockerio/php7-fpm date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )
               ->once()
               ->andReturn( $runner );

        // Assert global would start.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( GlobalStart::class, [], OutputStyle::class );

        // Assert the local project would start.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( DockerCompose::class, [
                   '--project-name',
                   'squareone',
                   'up',
                   '-d',
                   '--force-recreate',
               ] );

        // Assert prestissimo is installed in the php-fpm container
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( DockerCompose::class, [
                   '--project-name',
                   'squareone',
                   'exec',
                   'php-fpm',
                   'composer',
                   'global',
                   'require',
                   'hirak/prestissimo',
               ] );

        // Assert composer install would be run.
        Artisan::shouldReceive( 'call' )->once()
               ->with( Composer::class, [
                   'args' => [
                       'install',
                   ],
               ], OutputStyle::class );

        // Assert open command is called when passed.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( Open::class, [
                   'url' => 'https://squareone.tribe',
               ] );

        $command = $this->app->make( Start::class );

        // Run command pass a git token when requested.
        $tester = $this->runCommand( $command, [ '--browser' => true, '--path' => storage_path( 'tests' ) ], [
            'Enter your license key for WP_PLUGIN_ACF_KEY (input is hidden)'    => '123456',
            'Enter your license key for WP_PLUGIN_GF_KEY (input is hidden)'     => '123456',
            'Enter your license key for WP_PLUGIN_GF_TOKEN (input is hidden)'   => '123456',
            'Enter your license key for WP_CUSTOM_PLUGIN_KEY (input is hidden)' => '123456',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Starting squareone', $tester->getDisplay() );
        $this->assertStringContainsString( 'Project started: https://squareone.tribe', $tester->getDisplay() );
        $this->assertStringContainsString( 'Project started: https://squareone.tribe', $tester->getDisplay() );
    }

    public function test_it_can_start_a_project_with_custom_env_file_with_defaults() {
        $config = $this->mock( Config::class );
        $config->shouldReceive( 'getComposerVolume' )
               ->twice()
               ->andReturn( storage_path( 'tests/dev/docker/composer' ) );

        $config->shouldReceive( 'getProjectName' )->times( 4 )->andReturn( 'squareone' );
        $config->shouldReceive( 'getProjectRoot' )->times( 4 )->andReturn( storage_path( 'tests' ) );
        $config->shouldReceive( 'getDockerDir' )->twice()->andReturn( storage_path( 'tests/dev/docker' ) );
        $config->shouldReceive( 'getProjectUrl' )->once()->andReturn( 'https://squareone.tribe' );
        $config->shouldReceive( 'setPath' )
               ->once()
               ->with( storage_path( 'tests' ) )
               ->andReturnSelf();

        $certHandler = $this->mock( Handler::class );
        $certHandler->shouldReceive( 'caExists' )->once()->andReturnTrue();
        $certHandler->shouldReceive( 'createCertificate' )->once();

        $env = $this->mock( Env::class );

        $env->shouldReceive( 'exists' )
            ->once()
            ->andReturnFalse();

        $env->shouldReceive( 'save' )
            ->once()
            ->with( $this->getDefaultEnv() );

        // Project's env files contains custom values from the default
        $env->shouldReceive( 'diff' )
            ->once()
            ->with( storage_path( 'tests/.env.sample' ) )
            ->andReturn( [
                'WP_CUSTOM_PLUGIN_KEY' => '',
                'CONSTANT_VALUE'       => 'filled',
            ] );

        $env->shouldReceive( 'getVars' )->once()->andReturn( [
            'WP_PLUGIN_ACF_KEY'  => '123456',
            'WP_PLUGIN_GF_KEY'   => '123456',
            'WP_PLUGIN_GF_TOKEN' => '123456',
        ] );

        $github = $this->mock( Github::class );

        $github->shouldReceive( 'exists' )
               ->once()
               ->andReturnTrue();

        $github->shouldReceive( 'copy' )
               ->once()
               ->with( storage_path( 'tests/dev/docker/composer' ) )
               ->andReturnTrue();

        $filesystem = $this->mock( Filesystem::class );

        $filesystem->shouldReceive( 'get' )
                   ->once()
                   ->with( storage_path( 'defaults/env' ) )
                   ->andReturn( $this->getDefaultEnv() );

        $filesystem->shouldReceive( 'exists' )
                   ->once()
                   ->with( storage_path( 'tests/.env.sample' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/.env' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'put' )
                   ->once()
                   ->with( storage_path( 'tests/.env' ),
                       "WP_PLUGIN_ACF_KEY='123456'" . PHP_EOL . "WP_PLUGIN_GF_KEY='123456'" . PHP_EOL . "WP_PLUGIN_GF_TOKEN='123456'" . PHP_EOL
                       . "WP_CUSTOM_PLUGIN_KEY='123456'" . PHP_EOL . "CONSTANT_VALUE='filled'" . PHP_EOL )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/dev/docker/composer/auth.json' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/dev/docker/composer/composer.lock' ) )
                   ->andReturnTrue();


        $runner = $this->mock( CommandRunner::class );
        $runner->shouldReceive( 'throw' )->once()->andReturn( $runner );

        // Assert vm time sync runs.
        $runner->shouldReceive( 'run' )
               ->with( 'docker run --privileged --rm phpdockerio/php7-fpm date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )
               ->once()
               ->andReturn( $runner );

        // Assert global would start.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( GlobalStart::class, [], OutputStyle::class );

        // Assert the local project would start.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( DockerCompose::class, [
                   '--project-name',
                   'squareone',
                   'up',
                   '-d',
                   '--force-recreate',
               ] );

        // Assert prestissimo is installed in the php-fpm container
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( DockerCompose::class, [
                   '--project-name',
                   'squareone',
                   'exec',
                   'php-fpm',
                   'composer',
                   'global',
                   'require',
                   'hirak/prestissimo',
               ] );

        // Assert composer install would be run.
        Artisan::shouldReceive( 'call' )->once()
               ->with( Composer::class, [
                   'args' => [
                       'install',
                   ],
               ], OutputStyle::class );

        // Assert open command is called when passed.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( Open::class, [
                   'url' => 'https://squareone.tribe',
               ] );

        $command = $this->app->make( Start::class );

        // Run command pass a git token when requested.
        $tester = $this->runCommand( $command, [ '--browser' => true, '--path' => storage_path( 'tests' ) ], [
            'Enter your license key for WP_PLUGIN_ACF_KEY (input is hidden)'    => '123456',
            'Enter your license key for WP_PLUGIN_GF_KEY (input is hidden)'     => '123456',
            'Enter your license key for WP_PLUGIN_GF_TOKEN (input is hidden)'   => '123456',
            'Enter your license key for WP_CUSTOM_PLUGIN_KEY (input is hidden)' => '123456',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Starting squareone', $tester->getDisplay() );
        $this->assertStringContainsString( 'Project started: https://squareone.tribe', $tester->getDisplay() );
        $this->assertStringContainsString( 'Project started: https://squareone.tribe', $tester->getDisplay() );
    }

    public function test_it_can_start_a_project_with_custom_env_file_with_custom_env_vars() {
        $config = $this->mock( Config::class );
        $config->shouldReceive( 'getComposerVolume' )
               ->twice()
               ->andReturn( storage_path( 'tests/dev/docker/composer' ) );

        $config->shouldReceive( 'getProjectName' )->times( 4 )->andReturn( 'squareone' );
        $config->shouldReceive( 'getProjectRoot' )->times( 4 )->andReturn( storage_path( 'tests' ) );
        $config->shouldReceive( 'getDockerDir' )->twice()->andReturn( storage_path( 'tests/dev/docker' ) );
        $config->shouldReceive( 'getProjectUrl' )->once()->andReturn( 'https://squareone.tribe' );
        $config->shouldReceive( 'setPath' )
               ->once()
               ->with( storage_path( 'tests' ) )
               ->andReturnSelf();

        $certHandler = $this->mock( Handler::class );
        $certHandler->shouldReceive( 'caExists' )->once()->andReturnTrue();
        $certHandler->shouldReceive( 'createCertificate' )->once();

        $env = $this->mock( Env::class );

        $env->shouldReceive( 'exists' )
            ->once()
            ->andReturnFalse();

        $env->shouldReceive( 'save' )
            ->once()
            ->with( $this->getDefaultEnv() );

        // Project's env files contains custom values from the default
        $env->shouldReceive( 'diff' )
            ->once()
            ->with( storage_path( 'tests/.env.sample' ) )
            ->andReturn( [
                'WP_CUSTOM_PLUGIN_KEY' => '',
            ] );

        $env->shouldReceive( 'getVars' )->once()->andReturn( [
            'WP_PLUGIN_ACF_KEY'  => '123456',
            'WP_PLUGIN_GF_KEY'   => '123456',
            'WP_PLUGIN_GF_TOKEN' => '123456',
        ] );

        $github = $this->mock( Github::class );

        $github->shouldReceive( 'exists' )
               ->once()
               ->andReturnTrue();

        $github->shouldReceive( 'copy' )
               ->once()
               ->with( storage_path( 'tests/dev/docker/composer' ) )
               ->andReturnTrue();

        $filesystem = $this->mock( Filesystem::class );

        $filesystem->shouldReceive( 'get' )
                   ->once()
                   ->with( storage_path( 'defaults/env' ) )
                   ->andReturn( $this->getDefaultEnv() );

        $filesystem->shouldReceive( 'exists' )
                   ->once()
                   ->with( storage_path( 'tests/.env.sample' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/.env' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'put' )
                   ->once()
                   ->with( storage_path( 'tests/.env' ),
                       "WP_PLUGIN_ACF_KEY='123456'" . PHP_EOL . "WP_PLUGIN_GF_KEY='123456'" . PHP_EOL . "WP_PLUGIN_GF_TOKEN='123456'" . PHP_EOL
                       . "WP_CUSTOM_PLUGIN_KEY='123456'" . PHP_EOL )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/dev/docker/composer/auth.json' ) )
                   ->andReturnTrue();

        $filesystem->shouldReceive( 'missing' )
                   ->once()
                   ->with( storage_path( 'tests/dev/docker/composer/composer.lock' ) )
                   ->andReturnTrue();


        $runner = $this->mock( CommandRunner::class );
        $runner->shouldReceive( 'throw' )->once()->andReturn( $runner );

        // Assert vm time sync runs.
        $runner->shouldReceive( 'run' )
               ->with( 'docker run --privileged --rm phpdockerio/php7-fpm date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )
               ->once()
               ->andReturn( $runner );

        // Assert global would start.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( GlobalStart::class, [], OutputStyle::class );

        // Assert the local project would start.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( DockerCompose::class, [
                   '--project-name',
                   'squareone',
                   'up',
                   '-d',
                   '--force-recreate',
               ] );

        // Assert prestissimo is installed in the php-fpm container
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( DockerCompose::class, [
                   '--project-name',
                   'squareone',
                   'exec',
                   'php-fpm',
                   'composer',
                   'global',
                   'require',
                   'hirak/prestissimo',
               ] );

        // Assert composer install would be run.
        Artisan::shouldReceive( 'call' )->once()
               ->with( Composer::class, [
                   'args' => [
                       'install',
                   ],
               ], OutputStyle::class );

        // Assert open command is called when passed.
        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( Open::class, [
                   'url' => 'https://squareone.tribe',
               ] );

        $command = $this->app->make( Start::class );

        // Run command pass a git token when requested.
        $tester = $this->runCommand( $command, [ '--browser' => true, '--path' => storage_path( 'tests' ) ], [
            'Enter your license key for WP_PLUGIN_ACF_KEY (input is hidden)'    => '123456',
            'Enter your license key for WP_PLUGIN_GF_KEY (input is hidden)'     => '123456',
            'Enter your license key for WP_PLUGIN_GF_TOKEN (input is hidden)'   => '123456',
            'Enter your license key for WP_CUSTOM_PLUGIN_KEY (input is hidden)' => '123456',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Starting squareone', $tester->getDisplay() );
        $this->assertStringContainsString( 'Project started: https://squareone.tribe', $tester->getDisplay() );
        $this->assertStringContainsString( 'Project started: https://squareone.tribe', $tester->getDisplay() );
    }

    private function getDefaultEnv(): string {
        return "WP_PLUGIN_ACF_KEY='123456'" . PHP_EOL . "WP_PLUGIN_GF_KEY='123456'" . PHP_EOL . "WP_PLUGIN_GF_TOKEN='123456'" . PHP_EOL;
    }

}
