<?php declare(strict_types=1);

namespace App\Listeners;

use App\Services\Migrations\Migrator;
use App\Services\Update\Updater;
use Composer\Semver\Comparator;
use Filebase\Document;
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
	 */
	protected Finder $finder;

	/**
	 * The updater.
	 */
	protected Updater $updater;

	/**
	 * The migrator.
	 */
	protected Migrator $migrator;

	/**
	 * The running application's version.
	 */
	protected string $version;

	/**
	 * UpdateCheck constructor.
	 *
	 * @param  \Symfony\Component\Finder\Finder   $finder
	 * @param  \App\Services\Migrations\Migrator  $migrator
	 * @param  \App\Services\Update\Updater       $updater
	 * @param  string                             $version
	 */
	public function __construct( Finder $finder, Migrator $migrator, Updater $updater, string $version ) {
		$this->finder   = $finder;
		$this->migrator = $migrator;
		$this->updater  = $updater;
		$this->version  = $version;
	}

	/**
	 * Run the update checker.
	 *
	 * @param  \Illuminate\Console\Events\CommandStarting  $event
	 *
	 * @return bool
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
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
		if ( 'testing' === env( 'APP_ENV' ) && '1' !== env( 'ALLOW_MIGRATION' ) ) {
			return false;
		}

		if ( empty( $command ) ) {
			return false;
		}

		if ( 'self' === substr( $command, 0, 4 ) || 'app' === substr( $command, 0, 3 ) ) {
			return false;
		}

		$release = $this->getRelease();

		if ( empty( $release->version ) ) {
			return true;
		}

		return Comparator::greaterThan( $this->version, $release->version );
	}

	/**
	 * Get release data.
	 *
	 * @return \Filebase\Document
	 */
	protected function getRelease(): Document {
		return $this->updater->getCachedRelease();
	}

	/**
	 * Run the migrator.
	 *
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 *
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function runMigration( OutputInterface $output ): void {
		$this->finder->files()->name( '*.php' )->in( storage_path( 'migrations' ) );

		if ( iterator_count( $this->finder ) <= 0 ) {
			return;
		}

		$this->migrator->run( $this->finder, $output );
	}

}
