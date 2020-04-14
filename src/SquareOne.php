<?php declare( strict_types=1 );

namespace Tribe\Sq1;

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
use Tribe\Sq1\Commands\SquareOneCommand;
use Tribe\Sq1\Commands\UpdateCommands;
use Tribe\Sq1\Hooks\CertificateHandler;
use Tribe\Sq1\Hooks\Hook;
use Tribe\Sq1\Hooks\ResolverHandler;
use Tribe\Sq1\Models\Certificate;
use Tribe\Sq1\Commands\ComposerCommands;
use Tribe\Sq1\Commands\GlobalDockerCommands;
use Tribe\Sq1\Commands\LocalDockerCommands;
use Tribe\Sq1\Models\OperatingSystem;

/**
 * The Square One Global Docker Application
 *
 * @package Tribe\Sq1
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
			\Tribe\Sq1\Hooks\ResolverHandler::class,
			\Tribe\Sq1\Hooks\Docker::class,
			\Tribe\Sq1\Hooks\Update::class,
			\Tribe\Sq1\Commands\GlobalDockerCommands::class,
			\Tribe\Sq1\Commands\ComposerCommands::class,
			\Tribe\Sq1\Commands\LocalDockerCommands::class,
			\Tribe\Sq1\Commands\WpCliCommands::class,
			\Tribe\Sq1\Commands\ShellCommands::class,
			\Tribe\Sq1\Commands\GulpCommands::class,
			\Tribe\Sq1\Commands\TestCommands::class,
			\Tribe\Sq1\Commands\ConfigCommands::class,
			\Tribe\Sq1\Commands\UpdateCommands::class,
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

}
