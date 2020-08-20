<?php declare( strict_types=1 );

namespace App\Services;

use App\Contracts\Runner;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Bootstrap a local SquareOne project.
 *
 * @package App\Services
 */
class ProjectBootstrapper {

    /**
     * Illuminate filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The home directory instance.
     *
     * @var \App\Services\HomeDir
     */
    protected $homedir;

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * ProjectCreator constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Services\HomeDir              $homedir
     * @param  \App\Contracts\Runner              $runner
     */
    public function __construct( Filesystem $filesystem, HomeDir $homedir, Runner $runner ) {
        $this->filesystem = $filesystem;
        $this->homedir    = $homedir;
        $this->runner     = $runner;
    }

    /**
     * Rename object-cache.php as we sometimes get cached database results.
     *
     * @param  string  $projectRoot
     *
     * @return \App\Services\ProjectBootstrapper
     */
    public function renameObjectCache( string $projectRoot ): self {
        $objectCache = $projectRoot . '/wp-content/object-cache.php';

        if ( $this->filesystem->exists( $objectCache ) ) {
            $this->filesystem->move( $objectCache, str_replace( 'object-cache.php', 'object-cache.bak.php', $objectCache ) );
        }

        return $this;
    }

    /**
     * Restore the Object cache after.
     *
     * @param  string  $projectRoot
     *
     * @return \App\Services\ProjectBootstrapper
     */
    public function restoreObjectCache( string $projectRoot ): self {
        $objectCache = $projectRoot . '/wp-content/object-cache.bak.php';

        if ( $this->filesystem->exists( $objectCache ) ) {
            $this->filesystem->move( $objectCache, str_replace( 'object-cache.bak.php', 'object-cache.php', $objectCache ) );
        }

        return $this;
    }

    /**
     * Create WordPress and test databases.
     *
     * @param  string  $projectName
     *
     * @return \App\Services\ProjectBootstrapper
     */
    public function createDatabases( string $projectName ): self {
        $projectName = str_replace( '-', '_', $projectName );

        // Create the WordPress database
        $this->runner->run( sprintf(
            'docker exec -i tribe-mysql mysql -uroot -ppassword <<< "CREATE DATABASE tribe_%s;"',
            $projectName
        ) );

        // Create test databases
        $this->runner->run( sprintf(
            'docker exec -i tribe-mysql mysql -uroot -ppassword <<< "CREATE DATABASE tribe_%s_tests; CREATE DATABASE tribe_%s_acceptance;"',
            $projectName,
            $projectName
        ) );

        return $this;
    }

    /**
     * Create local-config.php based off of local-config-sample.php
     *
     * @param  string  $projectRoot
     *
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function createLocalConfig( string $projectRoot ): bool {
        $sample = $projectRoot . '/local-config-sample.php';
        $config = $projectRoot . '/local-config.php';

        if ( $this->filesystem->exists( $config ) ) {
            return false;
        }

        $this->filesystem->copy( $sample, $config );

        $content = $this->filesystem->get( $sample );

        $content = str_replace( '//define( \'TRIBE_GLOMAR\', false );', 'define( \'TRIBE_GLOMAR\', false );', $content );

        return (bool) $this->filesystem->put( $config, $content );
    }

    /**
     * Create local-config.json, based on old and new SquareOne paths.
     *
     * @param  string  $projectRoot
     * @param  string  $projectDomain
     *
     * @return \App\Services\ProjectBootstrapper
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function createLocalConfigJson( string $projectRoot, string $projectDomain ): self {
        $files = [
            $projectRoot . '/local-config-sample.json',
            $projectRoot . '/wp-content/themes/core/local-config-sample.json',
        ];

        $file = array_filter( $files, 'file_exists' );

        if ( empty( $file ) ) {
            return $this;
        }

        $file = current( $file );

        $content = $this->filesystem->get( $file );
        $content = str_replace( 'square1.tribe', $projectDomain, $content );
        $content = str_replace( '"certs_path": ""', sprintf( '"certs_path": "%s/.config/squareone/global/certs"', $this->homedir->get() ), $content );

        $file = str_replace( '-sample', '', $file );

        if ( $this->filesystem->missing( $file ) ) {
            $this->filesystem->put( $file, $content );
        }

        return $this;
    }

    /**
     * Try to build the frontend of the project.
     *
     * @param  string                                             $projectRoot
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return \App\Services\ProjectBootstrapper
     */
    public function buildFrontend( string $projectRoot, OutputInterface $output ): self {
        $isThemeBuild = $projectRoot . '/wp-content/themes/core/package.json';

        $output->writeln( 'Building frontend assets, this will take a while...' );

        if ( $this->filesystem->exists( $isThemeBuild ) ) {
            $this->runner->run( '. ~/.nvm/nvm.sh && nvm install && nvm use && npm install -g gulp-cli && npm run install:theme' )->throw();

            $this->runner->with( [
                'path' => $projectRoot . '/wp-content/themes/core/',
            ] )->run( '. ~/.nvm/nvm.sh && nvm install && nvm use && npm run --prefix {{ $path }} gulp -- dist' )->throw();

        } else {
            $command = '. ~/.nvm/nvm.sh && nvm install && nvm use && yarn install';

            if ( $this->filesystem->exists( $projectRoot . '/gulpfile.js' ) ) {
                $command .= ' && npm install -g gulp-cli && gulp dist';
            } elseif ( $this->filesystem->exists( $projectRoot . '/Gruntfile.js' ) ) {
                $command .= ' && npm install -g grunt-cli && grunt dist';
            }

            $this->runner->run( $command )->throw();
        }

        return $this;
    }

}
