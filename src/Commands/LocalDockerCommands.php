<?php declare( strict_types=1 );

namespace Tribe\Sq1\Commands;

use Robo\Robo;
use Tribe\Sq1\Contracts\CertificateAwareInterface;
use Tribe\Sq1\Models\Certificate;
use Tribe\Sq1\Models\LocalDocker;
use Tribe\Sq1\Traits\InflectionAwareTrait;
use Tribe\Sq1\Traits\LocalAwareTrait;

/**
 * Local SquareOne Docker/Project Commands
 *
 * @package Tribe\Sq1\Tasks
 */
class LocalDockerCommands extends SquareOneCommand implements CertificateAwareInterface {

	use LocalAwareTrait;
	use InflectionAwareTrait;

	/**
	 * The Certificate Model
	 *
	 * @var Certificate
	 */
	protected $certificate;

	/**
	 * Set the Certificate model
	 *
	 * @param  \Tribe\Sq1\Models\Certificate  $cert
	 *
	 * @return mixed|void
	 */
	public function setCertificate( Certificate $cert ): void {
		$this->certificate = $cert;
	}

	/**
	 * Get the Certificate model
	 *
	 * @return \Tribe\Sq1\Models\Certificate
	 */
	public function getCertificate(): Certificate {
		return $this->certificate;
	}

	/**
	 * Starts your local SquareOne project, run anywhere in a project folder
	 *
	 * @command start
	 */
	public function start(): self {
		// Start global containers
		$this->globalTask->globalStart();

		$composer_cache = Robo::config()->get( LocalDocker::CONFIG_DOCKER_DIR ) . '/composer-cache';

		if ( ! is_dir( $composer_cache ) ) {
			mkdir( $composer_cache );
		}

		$composer_config = Robo::config()->get( LocalDocker::CONFIG_DOCKER_DIR ) . '/composer/auth.json';

		if ( ! is_file( $composer_config ) ) {
			$this->runComposerConfig();
		}

		$this->say( sprintf( 'Starting docker-compose project: %s', Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) ) );

		// Start the local project
		$this->syncVmtime()
		     ->taskDockerComposeUp()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
		     ->detachedMode()
		     ->forceRecreate()
		     ->run();

		$this->composerTask->composer( [ 'install' ] );

		$this->taskOpenBrowser( sprintf( 'https://%s.tribe', Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) ) )->run();

		return $this;
	}

	/**
	 * Stops your local SquareOne project, run anywhere in a project folder
	 *
	 * @command stop
	 */
	public function stop(): self {
		$this->taskDockerComposeDown()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
		     ->run();

		return $this;
	}

	/**
	 * Restarts your local SquareOne project
	 *
	 * @command restart
	 */
	public function restart() {
		$this->stop()->start();
	}

	/**
	 * Displays local SquareOne project docker logs
	 *
	 * @command logs
	 */
	public function logs() {
		$this->taskDockerComposeLogs()
			->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE) )
			->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
			->arg( '-f' )
			->run();
	}

	/**
	 * Writes a user supplied GitHub token to the composer-config.json
	 */
	protected function runComposerConfig(): void {
		$token =
			$this->ask( 'We have detected you have not configured a GitHub oAuth token. Please go to https://github.com/settings/tokens/new?scopes=repo and create one. Paste the token here:' );

		$this->taskWriteToFile( Robo::config()->get( LocalDocker::CONFIG_DOCKER_DIR ) . '/composer/auth.json' )
		     ->line( sprintf( '{ "github-oauth": { "github.com": "%s" } }', trim( $token ) ) )
		     ->run();
	}

	/**
	 * Synchronize VM time with system time
	 *
	 * This is to fix a time sync bug on OSX, may not need it anymore.
	 *
	 * @return $this
	 */
	protected function syncVmTime(): self {
		$this->taskDockerRun( 'phpdockerio/php7-fpm' )
		     ->privileged()
		     ->args( [ '--rm' ] )
		     ->exec( 'date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )
		     ->printOutput( false )
		     ->silent( true )
		     ->run();

		return $this;
	}

}
