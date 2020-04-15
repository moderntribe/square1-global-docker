<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Tribe\SquareOne\Models\LocalDocker;
use Tribe\SquareOne\Traits\LocalAwareTrait;

/**
 * Class GulpCommands
 *
 * @package Tribe\SquareOne\Commands
 */
class GulpCommands extends SquareOneCommand {

	use LocalAwareTrait;

	public const NVM_SOURCE = '. ~/.nvm/nvm.sh && nvm use';

	/**
	 * Run a Gulp command
	 *
	 * @command gulp
	 *
	 * @param  array  $args The Gulp Command, e.g. dist, watch etc...
	 */
	public function gulp( array $args ) {
		$command = $this->prepareCommand( $args );

		$this->taskExec( self::NVM_SOURCE . " && gulp ${command}" )
			->dir( Robo::config()->get( LocalDocker::CONFIG_PROJECT_ROOT ) )
			->run();
	}
}
