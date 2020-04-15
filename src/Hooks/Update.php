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
				$output->writeln( sprintf( '<info>Migrating to %s...</info>', $this->version ) );

				$factory   = new MigrationFactory( $this->container );
				$migration = $factory->make();

				if ( $migration && $migration->up() ) {
					// Write this release to the .version file
					file_put_contents( $versionFile, $this->version );

					$output->writeln( sprintf( '<info>Migration to %s complete!</info>', $this->version ) );
				}
			}
		}
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
