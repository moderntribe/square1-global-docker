<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Migrations;

use League\Container\ContainerInterface;

/**
 * Class Migration
 *
 * @package Tribe\SquareOne\Migrations
 */
abstract class Migration {

	/**
	 * The container
	 *
	 * @var \League\Container\ContainerInterface
	 */
	protected $container;

	/**
	 * Migration constructor.
	 *
	 * @param  \League\Container\ContainerInterface  $container
	 */
	public function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	/**
	 * Run the Migration
	 *
	 * @return bool If the migration was successful
	 */
	abstract public function up(): bool;
}
