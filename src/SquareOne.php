<?php declare( strict_types=1 );

namespace Tribe\Sq1;

use Robo\Robo;
use Robo\Config\Config;
use Robo\Runner as RoboRunner;
use Robo\Common\ConfigAwareTrait;
use League\Container\ContainerAwareTrait;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Square One Global Docker Application
 *
 * @package Tribe\Sq1
 */
class SquareOne {

	use ConfigAwareTrait;
	use ContainerAwareTrait;

	const APPLICATION_NAME = 'Square One Global Docker';
	const REPOSITORY       = 'moderntribe/square1-global-docker';

	/**
	 * The Robo Runner.
	 *
	 * @var \Robo\Runner
	 */
	private $runner;

	/**
	 * The Application Version.
	 *
	 * @var string
	 */
	private $version;

	/**
	 * The Application.
	 *
	 * @var \Symfony\Component\Console\Application
	 */
	private $app;

	/**
	 * SquareOne constructor.
	 *
	 * @param  string                                                  $version
	 * @param  \Robo\Config\Config                                     $config
	 * @param  \Symfony\Component\Console\Input\InputInterface|null    $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface|null  $output
	 */
	public function __construct(
		string $version,
		Config $config,
		InputInterface $input = null,
		OutputInterface $output = null
	) {

		$this->version = $version;

		// Create application.
		$this->setConfig( $config );
		$this->app = new Application( self::APPLICATION_NAME, $this->version );

		// Create and configure container.
		$container = Robo::createDefaultContainer( $input, $output, $this->app, $config );
		$this->setContainer( $container );

		// Instantiate Robo Runner.
		$this->runner = new RoboRunner( $this->getTasks() );
		$this->runner->setContainer( $container );
		$this->runner->setSelfUpdateRepository( self::REPOSITORY );
	}

	/**
	 * Run a command.
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface    $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface  $output
	 *
	 * @return int The status code.
	 */
	public function run( InputInterface $input, OutputInterface $output ) {
		return $this->runner->run( $input, $output, $this->app, $this->getTasks() );
	}

	/**
	 * Add all Square One tasks here.
	 *
	 * @return array
	 */
	private function getTasks(): array {
		return [
			\Tribe\Sq1\Tasks\GlobalDockerTask::class,
			\Tribe\Sq1\Tasks\LocalDockerTask::class,
			\Tribe\Sq1\Tasks\ComposerTask::class,
			\Tribe\Sq1\Tasks\WpCliTask::class,
			\Tribe\Sq1\Tasks\ShellTask::class,
		];
	}

}
