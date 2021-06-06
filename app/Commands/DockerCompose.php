<?php declare(strict_types=1);

namespace App\Commands;

use App\Contracts\Runner;
use App\Recorders\ResultRecorder;
use App\Services\Docker\Local\Config;
use App\Services\Docker\Network;
use App\Services\Settings\Groups\AllSettings;

/**
 * Docker Compose Facade / Proxy Command
 *
 * @package App\Commands
 */
class DockerCompose extends BaseCommand {

	/**
	 * The signature of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $signature = 'docker-compose ';

	/**
	 * The description of the command.
	 *
	 * @var string
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
	 */
	protected $description = 'Pass through for docker-compose binary';

	/**
	 * The docker compose binary to use.
	 */
	protected string $binary;

	protected AllSettings $settings;

	/**
	 * DockerCompose constructor.
	 *
	 * @param \App\Services\Settings\Groups\AllSettings $settings
	 * @param string                                    $binary
	 */
	public function __construct( AllSettings $settings, string $binary = 'docker-compose' ) {
		parent::__construct();

		$this->settings = $settings;
		$this->binary   = $binary;

		// Allow this command to receive any options/arguments
		$this->ignoreValidationErrors();
	}

	/**
	 * Execute the console command.
	 *
	 * @param \App\Contracts\Runner             $runner   The command runner.
	 * @param \App\Services\Docker\Network      $network  The network manager.
	 * @param \App\Recorders\ResultRecorder     $recorder The command result recorder.
	 * @param \App\Services\Docker\Local\Config $config
	 *
	 * @return int
	 */
	public function handle( Runner $runner, Network $network, ResultRecorder $recorder, Config $config ): int {
		// Get the entire input passed to this command.
		$command = (string) $this->input;

		if ( ! str_contains( $this->binary, $command ) ) {
			$command = str_replace( 'docker-compose', $this->binary, $command );
		}

		$tty = true;

		if ( str_contains( $command, '-T' ) ) {
			$tty = false;
		}

		$vars = [
			Config::ENV_UID    => Config::uid(),
			Config::ENV_GID    => Config::gid(),
			Config::ENV_HOSTIP => $network->getGateWayIP(),
		];

		// Pass our docker compose files and environment variables
		if ( ! $this->isGlobal( $command ) ) {
			$vars    = array_merge( $vars, $this->getProjectEnvVars( $config ) );
			$command = $this->modifyCommand( $command, $config );
		}

		$response = $runner->output( $this )
						   ->tty( $tty )
						   ->withEnvironmentVariables( $vars )
						   ->run( $command );

		$recorder->add( $response->process()->getOutput() );

		return $response->ok() ? self::EXIT_SUCCESS : self::EXIT_ERROR;
	}

	/**
	 * If we're running a command on the global compose project.
	 *
	 * @param string $command
	 *
	 * @return bool
	 */
	protected function isGlobal( string $command ): bool {
		return str_contains( $command, '--project-name global' );
	}

	/**
	 * Environment variables for a SquareOne project.
	 *
	 * @param \App\Services\Docker\Local\Config $config
	 *
	 * @return array
	 */
	protected function getProjectEnvVars( Config $config ): array {
		return [
			Config::ENV_DB_NAME        => $config->getDbName(),
			Config::ENV_HOSTNAME       => $config->getProjectDomain(),
			Config::ENV_HOSTNAME_TESTS => $config->getProjectTestDomain(),
			Config::ENV_PROJECT_NAME   => $config->getProjectName(),
			Config::ENV_PROJECT_ROOT   => $config->getProjectRoot(),
		];
	}

	/**
	 * Replace command with our docker compose files.
	 *
	 * e.g. docker-compose -f services.yml -f nfs.yml
	 *
	 * @param string                            $command
	 * @param \App\Services\Docker\Local\Config $config
	 *
	 * @return string
	 */
	protected function modifyCommand( string $command, Config $config ): string {
		$args = [
			$this->binary,
			storage_path( 'docker/stacks/default.yml' ),
			//$config->getDockerDir() . '/docker-compose.yml',
			storage_path( sprintf( 'docker/volumes/%s.yml', $this->settings->docker->volume ) ),
		];

		return str_replace( $this->binary, vsprintf( '%s -f %s -f %s', $args ), $command );
	}

}
