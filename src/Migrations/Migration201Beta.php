<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Migrations;

/**
 * Migration to 2.0.1-beta
 *
 * @package Tribe\SquareOne\Migrations
 */
final class Migration201Beta extends Migration {

	/**
	 * Force cert.sh to +x for 2.0.0-beta users
	 *
	 * @return bool
	 */
	public function up(): bool {
		$config = $this->container->get( 'config' );

		return chmod( $config->get( 'docker.config-dir' ) . '/cert.sh', 0755 );
	}

}
