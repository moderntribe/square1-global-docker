<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\BaseCommand;
use App\Commands\LocalDocker\Bootstrap;
use App\Commands\LocalDocker\Start;
use App\Commands\LocalDocker\Wp;
use App\Commands\Open;
use App\Runners\CommandRunner;
use App\Services\HomeDir;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;


class BootstrapTest extends LocalDockerCommand {

    private $start;
    private $runner;
    private $homedir;

    public function setUp(): void {
        parent::setUp();

        $this->start = $this->mock( Start::class );
        $this->runner = $this->mock( CommandRunner::class );
        $this->homedir = $this->mock( HomeDir::class );

        $storage = Storage::disk( 'local' );
        $storage->makeDirectory( 'tests/' );
        $storage->makeDirectory( 'tests/dev/docker' );
        $storage->makeDirectory( 'tests/dev/composer' );
        $storage->put( 'tests/dev/docker/docker-compose.yml', 'test docker file' );
        $storage->put( 'tests/dev/composer/auth.json', 'test composer auth' );
        $storage->put( 'tests/local-config-sample.php', "//define( 'TRIBE_GLOMAR', false );" );
        $storage->put( 'tests/wp-content/object-cache.php', 'test object cache' );
        $storage->put( 'tests/local-config-sample.json', '"proxy": "square1.tribe", "certs_path": ""' );
        $storage->put( 'tests/wp-content/themes/core/package.json', 'test npm package.json' );
    }

    public function test_it_bootstraps_a_project() {
        Artisan::swap( $this->start );

        $this->config->shouldReceive( 'getProjectName' )->andReturn( 'squareone' );
        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests' ) );
        $this->config->shouldReceive( 'getComposerVolume' )->andReturn( storage_path( 'tests/dev/docker/composer' ) );
        $this->config->shouldReceive( 'getComposeFile' )->andReturn( $this->composeFile );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );
        $this->config->shouldReceive( 'getProjectUrl' )->andReturn( 'https://squareone.tribe' );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --privileged --rm phpdockerio/php7-fpm date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )
                     ->andReturnSelf();
        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $this->start->shouldReceive( 'call' )
                    ->once()
                    ->with( Start::class, [], OutputStyle::class );

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'docker exec -i tribe-mysql mysql -uroot -ppassword <<< "CREATE DATABASE tribe_squareone;"' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'docker exec -i tribe-mysql mysql -uroot -ppassword <<< "CREATE DATABASE tribe_squareone_tests; CREATE DATABASE tribe_squareone_acceptance;"' )
                     ->andReturnSelf();

        $this->start->shouldReceive( 'call' )
                    ->once()
                    ->with( Wp::class, [
                        'args' => [
                            'core',
                            'install',
                            '--url'            => 'squareone.tribe',
                            '--title'          => 'Square One',
                            '--admin_email'    => 'test@tri.be',
                            '--admin_user'     => 'admin',
                            '--admin_password' => 'test',
                            '--skip-email',
                        ],
                    ] );

        $this->start->shouldReceive( 'call' )
                    ->once()
                    ->with( Wp::class, [
                        'args' => [
                            'rewrite',
                            'structure',
                            '/%postname%/',
                        ],
                    ] );

        $this->start->shouldReceive( 'call' )
                    ->once()
                    ->with( Open::class, [
                        'url' => 'https://squareone.tribe',
                    ] );

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( '. ~/.nvm/nvm.sh && nvm install && nvm use && npm install -g gulp-cli && npm run install:theme' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [ 'path' => storage_path( 'tests/wp-content/themes/core/' ) ] )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( '. ~/.nvm/nvm.sh && nvm install && nvm use && npm run --prefix {{ $path }} gulp -- dist' )
                     ->andReturnSelf();

        $this->homedir->shouldReceive( 'get' )->once()->andReturn( '/home/test' );

        $command = $this->app->make( Bootstrap::class );

        $tester = $this->runCommand( $command, [], [
            'Enter your email address'  => 'test@tri.be',
            'Enter your admin username' => 'admin',
            'Enter your password'       => 'test',
            'Confirm your password'     => 'test',
        ] );

        $this->assertSame( BaseCommand::EXIT_SUCCESS, $tester->getStatusCode() );

        $this->assertFileExists( storage_path( 'tests/wp-content/object-cache.php' ) );
        $this->assertFileNotExists( storage_path( 'tests/wp-content/object-cache.bak.php' ) );

        $this->assertFileExists( storage_path( 'tests/local-config.php' ) );
        $this->assertSame( "define( 'TRIBE_GLOMAR', false );", file_get_contents( storage_path( 'tests/local-config.php' ) ) );
        $this->assertNotSame( "//define( 'TRIBE_GLOMAR', false );", file_get_contents( storage_path( 'tests/local-config.php' ) ) );

        $this->assertFileExists( storage_path( 'tests/local-config.json') );
        $this->assertSame( '"proxy": "squareone.tribe", "certs_path": "/home/test/.config/squareone/global/certs"', file_get_contents( storage_path( 'tests/local-config.json' ) ) );
    }

    public function test_it_fails_validation_with_invalid_input() {
        $command = $this->app->make( Bootstrap::class );

        $tester = $this->runCommand( $command, [], [
            'Enter your email address'  => 'invalid email',
            'Enter your admin username' => '',
            'Enter your password'       => 'test',
            'Confirm your password'     => 'wrong confirmation',
        ] );

        $this->assertSame( BaseCommand::EXIT_ERROR, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Invalid email address', $tester->getDisplay() );
        $this->assertStringContainsString( 'The username field is required', $tester->getDisplay() );
        $this->assertStringContainsString( 'The password and password confirmation must match', $tester->getDisplay() );
    }

}
