<?php

namespace Tests\Feature\Commands\LocalDocker;

use App\Services\FileIO;
use Flintstone\Flintstone;
use App\Runners\CommandRunner;
use App\Commands\LocalDocker\Share;
use Illuminate\Support\Facades\Storage;
use Flintstone\Formatter\JsonFormatter;

class ShareTest extends LocalDockerCommand {

    protected $settings;
    protected $runner;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests/share-test/wp-content/mu-plugins' );
        Storage::disk( 'local' )->makeDirectory( 'tests/store' );

        $this->settings = new Flintstone( 'settings', [
            'dir'       => storage_path( 'tests/store' ),
            'formatter' => new JsonFormatter(),
        ] );
        $this->runner   = $this->mock( CommandRunner::class );
    }

    public function test_it_shares_without_a_saved_ngrok_token() {
        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --rm -it --net global_proxy --link tribe-proxy wernight/ngrok ngrok http --authtoken {{ $token }} -host-header={{ $domain }} tribe-proxy:443' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->with( [
            'domain' => 'squareone.tribe',
            'token'  => 'mytoken',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'tty' )->with( true )->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command, [], [
            'mytoken',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Ngrok requires a free user account to proxy to https domains.', $tester->getDisplay() );
        $this->assertSame( 'mytoken', $this->settings->get( 'ngrok' ) );
    }

    public function test_it_fails_with_empty_ngrok_token() {
        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command, [], [
            '',
        ] );

        $this->assertSame( 1, $tester->getStatusCode() );
        $this->assertStringContainsString( 'No token entered', $tester->getDisplay() );
        $this->assertEmpty( $this->settings->get( 'ngrok' ) );
    }

    public function test_it_shares_with_a_saved_ngrok_token() {
        $this->settings->set( 'ngrok', 'savedtoken' );

        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --rm -it --net global_proxy --link tribe-proxy wernight/ngrok ngrok http --authtoken {{ $token }} -host-header={{ $domain }} tribe-proxy:443' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->with( [
            'domain' => 'squareone.tribe',
            'token'  => 'savedtoken',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'tty' )->with( true )->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringNotContainsString( 'Ngrok requires a free user account to proxy to https domains.', $tester->getDisplay() );
    }

    public function test_it_adds_mu_plugin_to_gitignore() {
        Storage::disk( 'local' )->put( 'tests/share-test/.gitignore', '*.sql' );

        $this->settings->set( 'ngrok', 'savedtoken' );

        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --rm -it --net global_proxy --link tribe-proxy wernight/ngrok ngrok http --authtoken {{ $token }} -host-header={{ $domain }} tribe-proxy:443' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->with( [
            'domain' => 'squareone.tribe',
            'token'  => 'savedtoken',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'tty' )->with( true )->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command, [], [ 'yes' ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Your project is missing ".local.php" in your .gitignore', $tester->getDisplay() );
        $this->assertStringContainsString( 'Added ".local.php" to .gitignore.', $tester->getDisplay() );
        $contents = file_get_contents( storage_path( 'tests/share-test/.gitignore' ) );
        $this->assertStringContainsString( '*.local.php', $contents );
    }

    public function test_it_shows_an_error_if_unable_to_write_to_gitignore() {
        Storage::disk( 'local' )->put( 'tests/share-test/.gitignore', '*.sql' );

        $file = $this->mock( FileIO::class );
        $file->shouldReceive( 'exists' )->andReturnTrue();
        $file->shouldReceive( 'contains' )->andReturnFalse();
        $file->shouldReceive( 'append_content' )->andReturnFalse();

        $this->settings->set( 'ngrok', 'savedtoken' );

        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --rm -it --net global_proxy --link tribe-proxy wernight/ngrok ngrok http --authtoken {{ $token }} -host-header={{ $domain }} tribe-proxy:443' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->with( [
            'domain' => 'squareone.tribe',
            'token'  => 'savedtoken',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'tty' )->with( true )->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command, [], [ 'yes' ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Unable to write to .gitignore.', $tester->getDisplay() );
    }

    public function test_it_bypass_git_ignore_functionality_when_it_already_exists() {
        Storage::disk( 'local' )->put( 'tests/share-test/.gitignore', '*.local.php' );

        $this->settings->set( 'ngrok', 'savedtoken' );

        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --rm -it --net global_proxy --link tribe-proxy wernight/ngrok ngrok http --authtoken {{ $token }} -host-header={{ $domain }} tribe-proxy:443' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->with( [
            'domain' => 'squareone.tribe',
            'token'  => 'savedtoken',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'tty' )->with( true )->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->andReturnSelf();

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringNotContainsString( 'Your project is missing', $tester->getDisplay() );
        $contents = file_get_contents( storage_path( 'tests/share-test/.gitignore' ) );
        $this->assertStringContainsString( '*.local.php', $contents );
    }

}
