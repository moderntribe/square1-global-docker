<?php declare( strict_types=1 );

namespace Tribe\Sq1\Tasks;

use Robo\Robo;
use Tribe\Sq1\Contracts\CertificateAwareInterface;
use Tribe\Sq1\Models\Certificate;
use Tribe\Sq1\Models\LocalDocker;
use Tribe\Sq1\Traits\InflectionAwareTrait;
use Tribe\Sq1\Traits\LocalAwareTrait;

/**
 * Local Docker/Project Commands
 *
 * @package Tribe\Sq1\Tasks
 */
class LocalDockerTask extends Sq1Task implements CertificateAwareInterface {

	use LocalAwareTrait;
	use InflectionAwareTrait;

	/**
	 * The Certificate Model.
	 *
	 * @var Certificate
	 */
	protected $certificate;

	/**
	 * Set the Certificate model.
	 *
	 * @param  \Tribe\Sq1\Models\Certificate  $cert
	 *
	 * @return mixed|void
	 */
	public function setCertificate( Certificate $cert ): void {
		$this->certificate = $cert;
	}

	/**
	 * Get the Certificate model.
	 *
	 * @return \Tribe\Sq1\Models\Certificate
	 */
	public function getCertificate(): Certificate {
		return $this->certificate;
	}

	/**
	 * Starts your local sq1 project, run anywhere in a sq1 project.
	 *
	 * @command start
	 */
	public function start(): self {
		$certPath = self::SCRIPT_PATH . sprintf( 'global/certs/%s.tribe.crt', Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) );

		$cert = $this->certificate->setCertPath( $certPath );

		// Generate a certificate for this project if it doesn't exist or if it expired.
		if ( ! $cert->exists() || $cert->expired() ) {
			$this->globalTask->globalCert( sprintf( '%s.tribe', Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) ) );
			$this->globalTask->globalRestart();
		}

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
		$this->taskDockerComposeUp()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
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
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
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
	 * Displays local docker project logs.
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

}
