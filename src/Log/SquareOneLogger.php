<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Log;

use Consolidation\Log\ConsoleLogLevel;
use Robo\Log\RoboLogger;
use Robo\Log\RoboLogLevel;
use Consolidation\Log\Logger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Replace RoboLogger with a less verbose version
 *
 * @see     RoboLogger
 *
 * @package Tribe\SquareOne\Log
 */
class SquareOneLogger extends Logger {

	/**
	 * SquareOneLogger constructor.
	 *
	 * @param   OutputInterface  $output
	 */
	public function __construct( OutputInterface $output ) {

		// Hide Robo's noisy success messages and set the same SIMULATED_ACTION as the default logger
		$verbosityOverrides = [
			ConsoleLogLevel::SUCCESS       => OutputInterface::VERBOSITY_VERBOSE,
			RoboLogLevel::SIMULATED_ACTION => OutputInterface::VERBOSITY_NORMAL,
		];

		parent::__construct( $output, $verbosityOverrides );
	}

}