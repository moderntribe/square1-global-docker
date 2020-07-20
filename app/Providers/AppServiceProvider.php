<?php

namespace App\Providers;

use App\Bootstrap;
use RuntimeException;
use Filebase\Database;
use App\Contracts\Runner;
use App\Services\HomeDir;
use App\Contracts\Trustable;
use App\Services\Config\Env;
use App\Services\Config\Github;
use App\Services\Certificate\Ca;
use App\Services\Update\Updater;
use App\Commands\Self\SelfUpdate;
use App\Recorders\ResultRecorder;
use App\Services\OperatingSystem;
use App\Commands\LocalDocker\Test;
use App\Commands\Self\UpdateCheck;
use Illuminate\Support\Collection;
use App\Commands\Config\ConfigCopy;
use App\Commands\GlobalDocker\Logs;
use App\Commands\GlobalDocker\Stop;
use App\Commands\Config\ComposeCopy;
use App\Commands\GlobalDocker\Start;
use App\Listeners\MigrationListener;
use App\Services\Docker\Dns\Factory;
use App\Services\Docker\Dns\Handler;
use App\Services\Docker\Local\Config;
use Illuminate\Filesystem\Filesystem;
use App\Commands\GlobalDocker\Restart;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Config\FileLocator;
use Illuminate\Contracts\Foundation\Application;
use App\Services\Docker\Dns\OsSupport\BaseSupport;
use App\Services\Certificate\Handler as CertHandler;
use App\Services\Certificate\Trust\LinuxTrustStore;
use App\Services\Certificate\Trust\Strategies\Linux;
use App\Services\Certificate\Trust\Strategies\MacOs;

/**
 * Class AppServiceProvider
 *
 * @codeCoverageIgnore
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot() {
        $bootstrap = $this->app->make( Bootstrap::class );
        $bootstrap->boot();

        $this->initConfig();
    }

    /**
     * Register any application services.
     *
     * @return void
     *
     */
    public function register() {
        $this->app->singleton( ResultRecorder::class );
        //$this->app->singleton( Config::class );

        $this->app->bind(
            'App\Contracts\Runner',
            'App\Runners\CommandRunner'
        );

        $this->app->when( Bootstrap::class )
                  ->needs( '$configDir' )
                  ->give( config( 'squareone.config-dir' ) );

        $this->app->when( Handler::class )
                  ->needs( BaseSupport::class )
                  ->give( function () {
                      $factory = new Factory(
                          $this->app->make( OperatingSystem::class ),
                          $this->app->make( Runner::class ),
                          $this->app->make( Filesystem::class )
                      );

                      return $factory->make( $this->app->make( Collection::class ) );
                  } );

        $this->app->when( Start::class )
                  ->needs( '$dockerComposeFile' )
                  ->give( config( 'squareone.docker.compose' ) );

        $this->app->when( Stop::class )
                  ->needs( '$dockerComposeFile' )
                  ->give( config( 'squareone.docker.compose' ) );

        $this->app->when( Restart::class )
                  ->needs( '$dockerComposeFile' )
                  ->give( config( 'squareone.docker.compose' ) );

        $this->app->when( Logs::class )
                  ->needs( '$dockerComposeFile' )
                  ->give( config( 'squareone.docker.compose' ) );

        $this->app->when( ConfigCopy::class )
                  ->needs( '$configDir' )
                  ->give( config( 'squareone.config-dir' ) );

        $this->app->when( ConfigCopy::class )
                  ->needs( '$downloadUrl' )
                  ->give( config( 'squareone.remote.squareone-yml' ) );

        $this->app->when( ComposeCopy::class )
                  ->needs( '$composeOverride' )
                  ->give( config( 'squareone.docker.compose-override' ) );

        $this->app->when( ComposeCopy::class )
                  ->needs( '$downloadUrl' )
                  ->give( config( 'squareone.remote.docker-compose' ) );

        $this->app->when( CertHandler::class )
                  ->needs( '$certFolder' )
                  ->give( config( 'squareone.docker.certs-folder' ) );

        $this->app->when( SelfUpdate::class )
                  ->needs( '$installedPhar' )
                  ->give( $this->getBinaryPath() );

        $this->app->when( SelfUpdate::class )
                  ->needs( '$appName' )
                  ->give( config( 'app.name' ) );

        $this->app->when( Updater::class )
                  ->needs( Database::class )
                  ->give( function () {
                      return new Database( [
                          'dir' => config( 'squareone.config-dir' ) . '/' . JsonStoreServiceProvider::DB_STORE . '/releases',
                      ] );
                  } );

        $this->app->when( UpdateCheck::class )
                  ->needs( '$version' )
                  ->give( function ( Application $app ) {
                      return $app->version();
                  } );

        $this->app->when( MigrationListener::class )
                  ->needs( '$version' )
                  ->give( function ( Application $app ) {
                      return $app->version();
                  } );

        $this->app->when( Test::class )
                  ->needs( '$container' )
                  ->give( config( 'squareone.tests.php-container' ) );

        $this->app->when( Ca::class )
                  ->needs( Trustable::class )
                  ->give( function ( Application $app ) {
                      /** @var OperatingSystem $os */
                      $os = ( $app->make( OperatingSystem::class ) )->getFamily();

                      switch ( $os ) {
                          case OperatingSystem::MAC_OS:
                              return $app->make( MacOs::class );
                          case OperatingSystem::LINUX:
                              /**
                               * Configure different Linux Trust Stores for different flavors
                               * e.g. RedHat, Debian, Arch etc...
                               */
                              $collection = collect( [
                                  new LinuxTrustStore( '/etc/pki/ca-trust/source/anchors/',
                                      '/etc/pki/ca-trust/source/anchors/%s.pem',
                                      'update-ca-trust extract' ),
                                  new LinuxTrustStore( '/usr/local/share/ca-certificates/',
                                      '/usr/local/share/ca-certificates/%s.crt',
                                      'update-ca-certificates' ),
                                  new LinuxTrustStore( '/etc/ca-certificates/trust-source/anchors/',
                                      '/etc/ca-certificates/trust-source/anchors/%s.crt',
                                      'trust extract-compat' ),
                                  new LinuxTrustStore( '/usr/share/pki/trust/anchors/',
                                      '/usr/share/pki/trust/anchors/%s.pem',
                                      'update-ca-certificates' ),
                              ] );

                              return $app->make( Linux::class, [ 'trustStores' => $collection ] );
                          default:
                              throw new RuntimeException( 'Operating system not supported' );
                      }
                  } );

        $this->app->when( Github::class )
                  ->needs( '$directory' )
                  ->give( config( 'squareone.config-dir' ) );

        $this->app->when( Env::class )
                  ->needs( '$directory' )
                  ->give( config( 'squareone.config-dir' ) );
    }

    /**
     * Load and parse the default squareone configuration file
     */
    protected function initConfig(): void {
        $files = $this->getConfigFiles();

        $yaml = $this->app->make( 'pragmarx.yaml' );

        foreach ( $files as $file ) {
            // Need to make sure it's not an empty .yml file: https://github.com/antonioribeiro/yaml/issues/21
            $contents = $yaml->parseFile( $file );

            if ( $contents ) {
                $yaml->loadToConfig( $file, 'squareone' );
            }
        }
    }

    /**
     * Load configuration files from multiple locations, furthest down the list will overwrite any configuration values of the previous one.
     *
     * @return array|string
     */
    private function getConfigFiles() {
        $paths = [
            config_path(),
            ( new HomeDir() )->get() . '/.config/squareone/',
            getcwd(),
        ];

        $fileLocator = new FileLocator( $paths );

        return $fileLocator->locate( 'squareone.yml', null, false );
    }

    /**
     * Get the path of the application's binary.
     *
     * @return string
     */
    private function getBinaryPath(): string {
        return realpath( $_SERVER['argv'][0] ) ?: $_SERVER['argv'][0];
    }

}
