<?php declare( strict_types=1 );

namespace App\Services;

use AlecRabbit\Snake\Contracts\SpinnerInterface;
use App\Contracts\Runner;
use App\Services\Docker\HealthChecker;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

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
     * The console spinner.
     *
     * @var \AlecRabbit\Snake\Contracts\SpinnerInterface
     */
    protected $spinner;

    /**
     * @var \App\Services\Docker\HealthChecker
     */
    protected $healthChecker;

    /**
     * ProjectCreator constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @param \App\Services\HomeDir $homedir
     * @param \App\Contracts\Runner $runner
     * @param SpinnerInterface $spinner
     * @param HealthChecker $healthChecker
     */
    public function __construct( Filesystem $filesystem, HomeDir $homedir, Runner $runner, SpinnerInterface $spinner, HealthChecker $healthChecker ) {
        $this->filesystem    = $filesystem;
        $this->homedir       = $homedir;
        $this->runner        = $runner;
        $this->spinner       = $spinner;
        $this->healthChecker = $healthChecker;
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
     * @param string $projectName
     * @param OutputInterface $output
     *
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     *
     * @return \App\Services\ProjectBootstrapper
     */
    public function createDatabases( string $projectName, OutputInterface $output ): self {
        $output->writeln( 'Waiting for database container to become active...' );

        while( $this->healthChecker->healthy( 'tribe-mysql' ) !== true ) {
            $this->spinner->spin();
            usleep( 500000 );
        }

        $this->spinner->end();

        $projectName = str_replace( '-', '_', $projectName );

        $existingDatabases = [];

        // Try to create the primary WordPress database.
        try {
            $this->runner->run( sprintf(
                'docker exec -i tribe-mysql mysql -uroot -ppassword -e "CREATE DATABASE tribe_%s;"',
                $projectName
            ) )->throw();
        } catch ( ProcessFailedException $e ) {
            if ( ! str_contains( $e->getMessage(), 'database exists' ) ) {
                throw $e;
            } else {
                $existingDatabases = [
                  sprintf( 'tribe_%s', $projectName ),
                ];
            }
        }

        // Try to create the test databases.
        try {
            $this->runner->run( sprintf(
                'docker exec -i tribe-mysql mysql -uroot -ppassword -e "CREATE DATABASE tribe_%s_tests; CREATE DATABASE tribe_%s_acceptance;"',
                $projectName,
                $projectName
            ) )->throw();
        } catch ( ProcessFailedException $e ) {
            if ( ! str_contains( $e->getMessage(), 'database exists' ) ) {
                throw $e;
            } else {
                $existingDatabases = array_merge( $existingDatabases, [
                    sprintf( 'tribe_%s_tests', $projectName ),
                    sprintf( 'tribe_%s_acceptance', $projectName ),
                ] );
            }
        }

        if ( $existingDatabases ) {
            $output->writeln( sprintf( '<question>Warning: one or more databases already exist: %s. Delete the databases and rerun this command if you run into problems.</question>', implode( ', ', $existingDatabases )  ) );
        }

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

        if ( $this->filesystem->exists( $config ) || $this->filesystem->missing( $sample ) ) {
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
            $this->runner->run( 'bash -c ". ~/.nvm/nvm.sh; nvm install; nvm use; npm install -g gulp-cli; npm run install:theme"' )->throw();

            $this->runner->with( [
                'path' => $projectRoot . '/wp-content/themes/core/',
            ] )->run( 'bash -c ". ~/.nvm/nvm.sh; nvm install; nvm use; npm run --prefix {{ $path }} gulp -- dist"' )->throw();

        } else {
            $command = 'bash -c ". ~/.nvm/nvm.sh; nvm install; nvm use; npm install -g yarn; yarn install;';

            if ( $this->filesystem->exists( $projectRoot . '/gulpfile.js' ) ) {
                $command .= ' npm install -g gulp-cli; gulp dist';
            } elseif ( $this->filesystem->exists( $projectRoot . '/Gruntfile.js' ) ) {
                $command .= ' npm install -g grunt-cli; grunt dist';
            }

            $command .= '"';

            $response = $this->runner->command( $command )->inBackground()->run();

            while ( $response->process()->isRunning() ) {
                usleep( 500000 );
                $this->spinner->spin();
            }

            $this->spinner->end();
        }

        return $this;
    }

}
