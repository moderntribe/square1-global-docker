<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Migrations;

use League\Container\ContainerInterface;

/**
 * Attempt to instantiate a Migration
 *
 * @package Tribe\SquareOne\Migrations
 */
class MigrationFactory {

	/**
	 * The container
	 *
	 * @var \League\Container\ContainerInterface
	 */
	private $container;

	/**
	 * The app version
	 *
	 * @var string
	 */
	private $version;

	/**
	 * MigrationFactory constructor.
	 *
	 * @param  \League\Container\ContainerInterface  $container
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
		$this->version   = $this->formatVersion( $this->container->get( 'application' )->getVersion() );
	}

	/**
	 * Instantiate a Migration
	 *
	 * @return \Tribe\SquareOne\Migrations\Migration|null
	 */
	public function make(): ?Migration {
		$class = sprintf( '\Tribe\SquareOne\Migrations\Migration%s', $this->version );

		if ( class_exists( $class ) ) {
			return new $class( $this->container );
		}

		return null;
	}

	/**
	 * Format a version to use as a class name
	 *
	 * @param  string  $version  The app version
	 *
	 * @return string The formatted version, e.g. 2.5.0-beta becomes 250Beta
	 */
	protected function formatVersion( string $version ): string {
		$version = ucwords( $version, '-' );
		$version = str_replace( '.', '', $version );
		$version = str_replace( '-', '', $version );

		return $version;
	}

}
