<?php declare( strict_types=1 );

namespace App\Listeners;

use App\Services\Migrations\MigrationChecker;
use App\Services\Migrations\Migrator;
use App\Services\Update\Updater;
use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run migrations, if available.
 *
 * @package App\Listeners
 */
class MigrationListener {

    /**
     * The symfony finder component.
     *
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * The updater.
     *
     * @var \App\Services\Update\Updater
     */
    protected $updater;

    /**
     * The migrator.
     *
     * @var \App\Services\Migrations\Migrator
     */
    protected $migrator;

    /**
     * The migration checker.
     *
     * @var \App\Services\Migrations\MigrationChecker
     */
    protected $migrationChecker;

    /**
     * The running application's version.
     *
     * @var string
     */
    protected $version;

    /**
     * UpdateCheck constructor.
     *
     * @param  Finder                                     $finder
     * @param  \App\Services\Migrations\Migrator          $migrator
     * @param  \App\Services\Migrations\MigrationChecker  $migrationChecker
     * @param  \App\Services\Update\Updater               $updater
     * @param  string                                     $version
     */
    public function __construct( Finder $finder, Migrator $migrator, MigrationChecker $migrationChecker, Updater $updater, string $version ) {
        $this->finder           = $finder;
        $this->migrator         = $migrator;
        $this->migrationChecker = $migrationChecker;
        $this->updater          = $updater;
        $this->version          = $version;
    }

    /**
     * Run the update checker.
     *
     * @param  \Illuminate\Console\Events\CommandStarting  $event
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return bool
     */
    public function handle( CommandStarting $event ): bool {
        if ( $this->shouldRun( $event->command ) ) {
            $this->runMigration( $event->output );

            return true;
        }

        return false;
    }

    /**
     * Check if we should run the update check.
     *
     * @param  string|null  $command
     *
     * @return bool
     */
    protected function shouldRun( ?string $command = '' ): bool {
        // Don't run during tests unless specified with the ALLOW_MIGRATION environment variable.
        if ( 'testing' === env( 'APP_ENV' ) && '1' != env( 'ALLOW_MIGRATION' ) ) {
            return false;
        }

        $command = (string) $command;

        if ( 'self' === substr( $command, 0, 4 ) || 'app' === substr( $command, 0, 3 ) ) {
            return false;
        }

        return $this->migrationChecker->shouldMigrate();
    }

    /**
     * Run the migrator.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function runMigration( OutputInterface $output ): void {
        $this->finder->files()->name( '*.php' )->in( storage_path( 'migrations' ) );

        if ( $this->finder->count() > 0 ) {
            $this->migrator->run( $this->finder, $output );
        }
    }

}
