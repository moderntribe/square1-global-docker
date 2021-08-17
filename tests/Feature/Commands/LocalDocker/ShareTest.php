<?php

namespace Tests\Feature\Commands\LocalDocker;

use Filebase\Database;
use Filebase\Document;
use App\Services\FileIO;
use App\Runners\CommandRunner;
use App\Commands\LocalDocker\Share;
use Illuminate\Support\Facades\Storage;

class ShareTest extends LocalDockerCommand {

    protected $settings;
    protected $runner;

    protected function setUp(): void {
        parent::setUp();

        Storage::disk( 'local' )->makeDirectory( 'tests/share-test/wp-content/mu-plugins' );

        $this->settings = $this->mock( Database::class );
        $this->runner   = $this->mock( CommandRunner::class );
    }

    public function test_it_shares_without_a_saved_ngrok_token() {
        $document = $this->mock( Document::class );
        $document->shouldReceive( 'save' )->once();

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

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
            'mytoken'
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Ngrok requires a free user account to proxy to https domains.', $tester->getDisplay() );
        $this->assertSame( 'mytoken', $document->ngrok_token );
    }

    public function test_it_fails_with_empty_ngrok_token() {
        $document = $this->mock( Document::class );

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command, [], [
            ''
        ] );

        $this->assertSame( 1, $tester->getStatusCode() );
        $this->assertStringContainsString( 'No token entered', $tester->getDisplay() );
        $this->assertEmpty( $document->ngrok_token );
    }

    public function test_it_shares_with_a_saved_ngrok_token() {
        $document = new Document( $this->settings );
        $document->ngrok_token = 'savedtoken';

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

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

    public function test_it_shares_with_a_custom_content_folder_and_a_saved_ngrok_token() {
        Storage::disk( 'local' )->makeDirectory( 'tests/share-test-custom-content-dir/content/mu-plugins' );

        $document = new Document( $this->settings );
        $document->ngrok_token = 'savedtoken';

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test-custom-content-dir' ) );
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
        $tester  = $this->runCommand( $command, [
            'directory' => 'content'
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringNotContainsString( 'Ngrok requires a free user account to proxy to https domains.', $tester->getDisplay() );
    }

    public function test_it_adds_mu_plugin_to_gitignore() {
        Storage::disk( 'local' )->put( 'tests/share-test/.gitignore', '*.sql' );

        $document = new Document( $this->settings );
        $document->ngrok_token = 'savedtoken';

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

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

        $document = new Document( $this->settings );
        $document->ngrok_token = 'savedtoken';

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

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

        $document = new Document( $this->settings );
        $document->ngrok_token = 'savedtoken';

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

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

    public function test_it_shows_error_if_custom_wp_content_folder() {
        Storage::disk( 'local' )->makeDirectory( 'tests/share-test-custom-content-dir/content/mu-plugins' );

        $document = new Document( $this->settings );
        $document->ngrok_token = 'savedtoken';

        $this->settings->shouldReceive( 'get' )->with( 'user_secrets' )->once()->andReturn( $document );

        $this->config->shouldReceive( 'getProjectRoot' )->andReturn( storage_path( 'tests/share-test-custom-content-dir' ) );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'squareone.tribe' );

        $command = new Share( $this->settings );
        $tester  = $this->runCommand( $command );

        $this->assertSame( 1, $tester->getStatusCode() );
        $this->assertStringContainsString( 'does not exist! Does this project have a renamed wp-content folder?', $tester->getDisplay() );
    }

}
