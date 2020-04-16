<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Hooks;

use Robo\Robo;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Tribe\SquareOne\Exceptions\SquareOneException;

/**
 * Docker Hooks
 *
 * @package Tribe\SquareOne\Hooks
 */
class Docker extends Hook {

	const VAR = 'HOSTIP';

	/**
	 * Set up Global Docker Configuration files
	 *
	 * @hook pre-init *
	 *
	 */
	public function setUp(): void {
		$dockerConfigFolder = Robo::config()->get( 'docker.config-dir' );

		if ( ! is_dir( $dockerConfigFolder ) ) {
			$filesystem = new Filesystem();
			$filesystem->mirror( $this->scriptPath . '/global', Robo::config()->get( 'docker.config-dir' ) );
			chmod( Robo::config()->get( 'docker.config-dir' ) . '/cert.sh', 0755 );
		}
	}

	/**
	 * Set the HOSTIP environment variable for use in docker-composer.yml.
	 *
	 * @hook init *
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Consolidation\AnnotatedCommand\AnnotationData   $data
	 *
	 * @throws \Tribe\SquareOne\Exceptions\SquareOneException
	 */
	public function saveDockerGatewayIP( InputInterface $input, AnnotationData $data ): void {
		$command = $data->get( 'command' );

		if ( 'global:start' === $command || 'global:restart' === $command || 'start' === $command || 'restart' === $command ) {
			$ip = $this->getDockerGatewayIP();

			if ( empty( $ip ) ) {
				throw new SquareOneException( 'Unable to get the Docker Gateway IP Address.' );
			}

			$result = putenv( sprintf( '%s=%s', self::VAR, $ip ) );

			if ( empty( $result ) ) {
				throw new SquareOneException( sprintf( 'Unable to set %s environment variable.', self::VAR ) );
			}
		}
	}

	/**
	 * Get the docker gateway IP address
	 *
	 * @return string The IP Address
	 *
	 * @throws \Tribe\SquareOne\Exceptions\SquareOneException
	 */
	protected function getDockerGatewayIP(): ?string {
		$process = new Process( [ 'docker', 'network', 'inspect', 'bridge' ] );
		$process->run();

		if ( ! $process->isSuccessful() ) {
			throw new SquareOneException( 'Unable to execute "docker network inspect bridge". Is docker installed?' );
		}

		$data = json_decode( $process->getOutput(), true );

		$ip = $data[0]['IPAM']['Config'][0]['Gateway'] ?? '';

		return filter_var( $ip, FILTER_VALIDATE_IP ) ?: null;
	}

}
