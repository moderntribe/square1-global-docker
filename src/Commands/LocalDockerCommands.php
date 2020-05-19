<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Commands;

use Robo\Robo;
use Tribe\SquareOne\Contracts\CertificateAwareInterface;
use Tribe\SquareOne\Models\Certificate;
use Tribe\SquareOne\Models\LocalDocker;
use Tribe\SquareOne\Traits\InflectionAwareTrait;
use Tribe\SquareOne\Traits\LocalAwareTrait;

/**
 * Local SquareOne Docker/Project Commands
 *
 * @package Tribe\SquareOne\Commands
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
	 * @param  \Tribe\SquareOne\Models\Certificate  $cert
	 *
	 * @return mixed|void
	 */
	public function setCertificate( Certificate $cert ): void {
		$this->certificate = $cert;
	}

	/**
	 * Get the Certificate model
	 *
	 * @return \Tribe\SquareOne\Models\Certificate
	 */
	public function getCertificate(): Certificate {
		return $this->certificate;
	}

	/**
	 * Starts your local SquareOne project, run anywhere in a project folder
	 *
	 * @command start
	 *
	 * @option  $browser|b Auto open the project in your default browser
	 *
	 * @param   array  $opts
	 *
	 */
	public function start( array $opts = [ 'browser|b' => false ] ): void {
		// Start global containers
		$this->globalTask->globalStart();

		$composer_cache = Robo::config()->get( LocalDocker::CONFIG_COMPOSER_CACHE );

		if ( ! is_dir( $composer_cache ) ) {
			mkdir( $composer_cache );
		}

		$composer_config = Robo::config()->get( LocalDocker::CONFIG_COMPOSER_AUTH );

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

		$uri = sprintf( 'https://%s.tribe', Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) );

		if ( ! empty( $opts['browser'] ) ) {
			$this->taskOpenBrowser( $uri )->run();
		} else {
			$this->say( sprintf( 'Project started at %s', $uri ) );
		}
	}

	/**
	 * Stops your local SquareOne project, run anywhere in a project folder
	 *
	 * @command stop
	 *
	 * @return self
	 */
	public function stop(): self {
		$projectName = Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME );

		$this->say( sprintf( 'Stopping project %s...', $projectName ) );

		$this->taskDockerComposeDown()
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( $projectName )
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
		     ->files( Robo::config()->get( LocalDocker::CONFIG_DOCKER_COMPOSE ) )
		     ->projectName( Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) )
		     ->arg( '-f' )
		     ->run();
	}

	/**
	 * Migrate a recently imported remote database to your local
	 *
	 * @command migrate-domain
	 */
	public function migrateDomain() {
		$wpCommand = $this->container->get( WpCliCommands::class . 'Commands' );

		$dbPrefix = $wpCommand->wp( [
			'db',
			'prefix',
		], [
			'return' => true,
		] );

		$dbPrefix = trim( $dbPrefix->getMessage() );

		$domain = $wpCommand->wp( [
			'db',
			'query',
			"SELECT option_value FROM ${dbPrefix}options WHERE option_name = 'siteurl'",
			'--skip-column-names',
		], [
			'return' => true,
		] );

		$domain       = trim( $domain->getMessage() );
		$sourceDomain = parse_url( $domain, PHP_URL_HOST );

		if ( empty( $sourceDomain ) ) {
			$this->yell( sprintf( 'Invalid siteurl found in options table: %s', $domain ) );
			exit( E_ERROR );
		}

		$targetDomain = Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) . '.tribe';

		if ( $sourceDomain === $targetDomain ) {
			$this->yell( sprintf( 'Error: Source and target domains match: %s.', $sourceDomain ) );
			exit( E_ERROR );
		}

		$confirm = $this->confirm( sprintf( 'Ready to search and replace "%s" to "%s" (This cannot be undone)?', $sourceDomain, $targetDomain ) );

		if ( ! $confirm ) {
			$this->say( 'Exiting...' );
			exit();
		}

		$wpCommand->wp( [
			'db',
			'query',
			"UPDATE ${dbPrefix}options SET option_value = REPLACE( option_value, '${sourceDomain}', '${targetDomain}' ) WHERE option_name = 'siteurl'",
		] );

		$wpCommand->wp( [
			'search-replace',
			"${sourceDomain}",
			"${targetDomain}",
			'--all-tables-with-prefix',
			'--verbose',
		] );

		$wpCommand->wp( [
			'cache',
			'flush',
		] );
	}

	/**
	 * Writes a user supplied GitHub token to the composer-config.json
	 */
	protected function runComposerConfig(): void {
		$token =
			$this->ask( 'We have detected you have not configured a GitHub oAuth token. Please go to https://github.com/settings/tokens/new?scopes=repo and create one. Paste the token here:' );

		$this->taskWriteToFile( Robo::config()->get( LocalDocker::CONFIG_COMPOSER_AUTH ) )
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
