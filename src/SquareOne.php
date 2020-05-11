<?php declare( strict_types=1 );

namespace Tribe\SquareOne;

use Robo\Robo;
use Robo\Application;
use Robo\Config\Config;
use Robo\Runner as RoboRunner;
use Robo\Common\ConfigAwareTrait;
use League\Container\ContainerAwareTrait;
use League\Container\ContainerAwareInterface;
use Robo\Contract\ConfigAwareInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tribe\SquareOne\Commands\SquareOneCommand;
use Tribe\SquareOne\Commands\UpdateCommands;
use Tribe\SquareOne\Hooks\CertificateHandler;
use Tribe\SquareOne\Hooks\Hook;
use Tribe\SquareOne\Hooks\ResolverHandler;
use Tribe\SquareOne\Hooks\Update;
use Tribe\SquareOne\Log\SquareOneLogger;
use Tribe\SquareOne\Models\Certificate;
use Tribe\SquareOne\Commands\ComposerCommands;
use Tribe\SquareOne\Commands\GlobalDockerCommands;
use Tribe\SquareOne\Commands\LocalDockerCommands;
use Tribe\SquareOne\Models\OperatingSystem;

/**
 * The Square One Global Docker Application
 *
 * @package Tribe\SquareOne
 */
class SquareOne implements ConfigAwareInterface, ContainerAwareInterface {

	use ConfigAwareTrait;
	use ContainerAwareTrait;

	const APPLICATION_NAME = 'SquareOne Global Docker';
	const REPOSITORY       = 'moderntribe/square1-global-docker';

	/**
	 * The Robo Runner.
	 *
	 * @var \Robo\Runner
	 */
	private $runner;

	/**
	 * The path of this script
	 *
	 * @var string
	 */
	private $scriptPath;

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
	 * @param  string                                                  $scriptPath
	 * @param  \Robo\Config\Config                                     $config
	 * @param  \Symfony\Component\Console\Input\InputInterface|null    $input
	 * @param  \Symfony\Component\Console\Output\OutputInterface|null  $output
	 */
	public function __construct(
		string $version,
		string $scriptPath,
		Config $config,
		InputInterface $input = null,
		OutputInterface $output = null
	) {

		$this->scriptPath = $scriptPath;
		$this->version    = $version;

		// Create application.
		$this->setConfig( $config );
		$this->app = new Application( self::APPLICATION_NAME, $this->version );
		$this->configureGlobalOptions();

		// Create and configure container.
		$container = Robo::createDefaultContainer( $input, $output, $this->app, $config );
		$this->setContainer( $container );
		$this->configureContainer();

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
	 * Add all SquareOne hooks and commands here.
	 *
	 * @return array
	 */
	private function getTasks(): array {
		return [
			CertificateHandler::class,
			\Tribe\SquareOne\Hooks\ResolverHandler::class,
			\Tribe\SquareOne\Hooks\Docker::class,
			\Tribe\SquareOne\Hooks\Update::class,
			\Tribe\SquareOne\Commands\GlobalDockerCommands::class,
			\Tribe\SquareOne\Commands\ComposerCommands::class,
			\Tribe\SquareOne\Commands\LocalDockerCommands::class,
			\Tribe\SquareOne\Commands\WpCliCommands::class,
			\Tribe\SquareOne\Commands\ShellCommands::class,
			\Tribe\SquareOne\Commands\GulpCommands::class,
			\Tribe\SquareOne\Commands\TestCommands::class,
			\Tribe\SquareOne\Commands\ConfigCommands::class,
			\Tribe\SquareOne\Commands\UpdateCommands::class,
		];
	}

	/**
	 * Configure the container.
	 */
	private function configureContainer(): void {
		$container = $this->getContainer();

		$container->share( Certificate::class );
		$container->add( 'os', OperatingSystem::class );

		$container->inflector( SquareOneCommand::class )
		          ->invokeMethod( 'setScriptPath', [ $this->scriptPath ] );

		$container->inflector( Hook::class )
		          ->invokeMethod( 'setScriptPath', [ $this->scriptPath ] )
		          ->invokeMethod( 'setOperatingSystem', [ 'os' ] );

		$container->inflector( LocalDockerCommands::class )
		          ->invokeMethod( 'setCertificate', [ Certificate::class ] )
		          ->invokeMethod( 'setGlobalDockerTask', [ GlobalDockerCommands::class . 'Commands' ] )
		          ->invokeMethod( 'setComposerTask', [ ComposerCommands::class . 'Commands' ] );

		$container->inflector( ResolverHandler::class )
		          ->invokeMethod( 'setDependencies', [] );

		$container->inflector( CertificateHandler::class )
		          ->invokeMethod( 'setDependencies', [ Certificate::class, $this->scriptPath ] );

		$container->inflector( UpdateCommands::class )
		          ->invokeMethod( 'setVersion', [ $this->version ] );

		$container->inflector( Update::class )
		          ->invokeMethod( 'setVersion', [ $this->version ] );

		// Override the RoboLogger class with our own, less verbose version
		$container->share( 'logger', SquareOneLogger::class )
		          ->withArgument( $container->get( 'output' ) )
		          ->withMethodCall( 'setLogOutputStyler', [ $container->get( 'logStyler' ) ] );
	}

	/**
	 * Add Global Command Options.
	 */
	private function configureGlobalOptions(): void {
		$this->app->getDefinition()->addOption(
			new InputOption( sprintf( '--%s', $this->config->get( 'options.project-path.name' ) ),
				sprintf( '-%s', $this->config->get( 'options.project-path.shortcut' ) ),
				InputOption::VALUE_REQUIRED,
				$this->config->get( 'options.project-path.description' )
			)
		);
	}

	/**
	 * Get the Application version
	 *
	 * @return string
	 */
	public function getVersion(): string {
		return $this->version;
	}

}
