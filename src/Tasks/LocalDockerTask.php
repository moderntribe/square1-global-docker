<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;
use Tribe\Sq1\Traits\InflectionAwareTrait;
use Tribe\Sq1\Traits\LocalAwareTrait;

/**
 * Local Docker/Project Commands
 *
 * @package Tribe\Sq1\Tasks
 */
class LocalDockerTask extends Sq1Task {

	use LocalAwareTrait;
	use InflectionAwareTrait;

	/**
	 * LocalDockerTask constructor.
	 *
	 * @throws \Tribe\Sq1\Exceptions\Sq1Exception
	 */
	public function __construct() {
		parent::__construct();
		// Set configuration variables.
		$this->getLocalDockerConfig( $this );
	}

	/**
	 * Starts your local sq1 project, run anywhere in a sq1 project.
	 *
	 * @command start
	 */
	public function start(): self {
		$cert = realpath( self::SCRIPT_PATH . sprintf( 'global/certs/%s.tribe.crt', Robo::config()->get( 'name' ) ) );

		// Generate a certificate for this project if it doesn't exist
		if ( false === $cert || ! is_file( $cert ) ) {
			$this->globalTask->globalCert( sprintf( '%s.tribe', Robo::config()->get( 'name' ) ) );
			$this->globalTask->globalRestart();
		}

		// Start global containers
		$this->globalTask->globalStart();

		$composer_cache = Robo::config()->get( 'docker_dir' ) . '/composer-cache';

		if ( ! is_dir( $composer_cache ) ) {
			mkdir( $composer_cache );
		}

		$composer_config = Robo::config()->get( 'docker_dir' ) . '/composer/auth.json';

		if ( ! is_file( $composer_config ) ) {
			$this->runComposerConfig();
		}

		$this->say( sprintf( 'Starting docker-compose project: %s', Robo::config()->get( 'name' ) ) );

		// Start the local project
		$this->taskDockerComposeUp()
		     ->files( Robo::config()->get( 'compose' ) )
		     ->projectName( Robo::config()->get( 'name' ) )
		     ->detachedMode()
		     ->forceRecreate()
		     ->run();

		$this->composerTask->composer( [ 'install' ] );

		return $this;
	}

	/**
	 * Stops your local sq1 project, run anywhere in a sq1 project.
	 *
	 * @command stop
	 */
	public function stop(): self {
		$this->taskDockerComposeDown()
		     ->files( Robo::config()->get( 'compose' ) )
		     ->projectName( Robo::config()->get( 'name' ) )
		     ->run();

		return $this;
	}

	/**
	 * Restarts your local sq1 project.
	 *
	 * @command restart
	 */
	public function restart() {
		$this->stop()->start();
	}

	/**
	 * Writes a user supplied GitHub token to the composer-config.json
	 */
	protected function runComposerConfig(): void {
		$token =
			$this->ask( 'We have detected you have not configured a GitHub oAuth token. Please go to https://github.com/settings/tokens/new?scopes=repo and create one. Paste the token here:' );

		$this->taskWriteToFile( Robo::config()->get( 'docker_dir' ) . '/composer/auth.json' )
		     ->line( sprintf( '{ "github-oauth": { "github.com": "%s" } }', trim( $token ) ) )
		     ->run();
	}

}
