<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Hooks;

use Robo\Robo;
use Composer\Semver\Comparator;
use League\Container\ContainerAwareInterface;
use League\Container\ContainerAwareTrait;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;
use Tribe\SquareOne\Commands\UpdateCommands;
use Tribe\SquareOne\Migrations\MigrationFactory;

/**
 * Update Hooks
 *
 * @package Tribe\SquareOne\Hooks
 */
class Update implements ContainerAwareInterface {

	use ContainerAwareTrait;

	public const INSTALLED_VERSION_FILE = '.version';

	/**
	 * The current phar version
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Perform updates from version to version
	 *
	 * @hook pre-init *
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Consolidation\AnnotatedCommand\AnnotationData   $data
	 */
	public function afterUpdate( InputInterface $input, AnnotationData $data ): void {
		$command = $data->get( 'command' );

		$output = Robo::output();

		if ( 'self:update-check' !== $command && 'self:update' !== $command ) {
			$versionFile = sprintf( '%s/%s', Robo::config()->get( 'squareone.config-dir' ), self::INSTALLED_VERSION_FILE );

			if ( ! file_exists( $versionFile ) ) {
				$shouldUpdate = true;
			} else {
				$version      = file_get_contents( $versionFile );
				$shouldUpdate = Comparator::greaterThan( $this->version, $version );
			}

			if ( $shouldUpdate ) {
				$factory   = new MigrationFactory( $this->container );
				$migration = $factory->make();

				if ( $migration ) {

					$output->writeln( sprintf( '<info>Migrating to %s...</info>', $this->version ) );

					if ( $migration->up() ) {
						$this->writeVersion( $versionFile );
						$output->writeln( sprintf( '<info>Migration to %s complete!</info>', $this->version ) );
					} else {
						$output->writeln( sprintf( '<error>Failed to run migration for version %s</error>', $this->version ) );
					}
				} else {
					// Nothing to do on this release, just update the version
					$this->writeVersion( $versionFile );
				}
			}
		}
	}

	/**
	 * Write the current version to the .version file
	 *
	 * @param  string  $versionFile  The path to the .version file
	 *
	 * @return bool
	 */
	protected function writeVersion( string $versionFile ): bool {
		return (bool) file_put_contents( $versionFile, $this->version );
	}

	/**
	 * Check for Updates when commands are run
	 *
	 * @hook init *
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Consolidation\AnnotatedCommand\AnnotationData   $data
	 */
	public function check( InputInterface $input, AnnotationData $data ): void {
		$command = $data->get( 'command' );

		if ( 'self:update-check' !== $command && 'self:update' !== $command ) {
			/** @var UpdateCommands $update */
			$update = $this->container->get( UpdateCommands::class . 'Commands' );
			$update->updateCheck( [ 'show-existing' => false ] );
		}
	}

	/**
	 * Set the current version, set via inflection
	 *
	 * @param  string  $version  The current phar version
	 *
	 * @return \Tribe\SquareOne\Hooks\Update
	 */
	public function setVersion( string $version ): self {
		$this->version = $version;

		return $this;
	}

}
