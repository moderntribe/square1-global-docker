<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Migrations;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Migration to 2.0.0-beta
 *
 * @package Tribe\SquareOne\Migrations
 */
final class Migration200Beta extends Migration {

	/**
	 * Run the Migration
	 *
	 * @return bool
	 */
	public function up(): bool {
		$config         = $this->container->get( 'config' );
		$newCertsFolder = $config->get( 'docker.certs-folder' );
		$oldCertsFolder = str_replace( 'squareone', 'sq1', $newCertsFolder );

		// Copy SSL certificates to new config directory
		if ( is_dir( $oldCertsFolder ) && ! is_dir( $newCertsFolder ) ) {
			$filesystem = new Filesystem();
			$filesystem->mirror( $oldCertsFolder, $newCertsFolder );
		}

		return true;
	}

}
