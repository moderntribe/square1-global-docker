<?php declare( strict_types=1 );

namespace Tribe\Sq1\Hooks;

use Robo\Robo;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Tribe\Sq1\Exceptions\Sq1Exception;

/**
 * Docker Hooks
 *
 * @package Tribe\Sq1\Hooks
 */
class Docker extends Hook {

	const VAR = 'HOSTIP';

	/**
	 * Set up Global Docker Configuration files
	 *
	 * @hook init *
	 *
	 */
	public function setUp(): void {
		$dockerConfigFolder = Robo::config()->get( 'docker.config' );

		if ( ! is_dir( $dockerConfigFolder ) ) {
			$filesystem = new Filesystem();
			$filesystem->mirror( $this->scriptPath . '/global', Robo::config()->get( 'docker.config' ) );
			chmod( Robo::config()->get( 'docker.config' ) . '/cert.sh', '0755' );
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
	 * @throws \Tribe\Sq1\Exceptions\Sq1Exception
	 */
	public function saveDockerGatewayIP( InputInterface $input, AnnotationData $data ): void {
		$command = $data->get( 'command' );

		if ( 'global:start' === $command || 'global:restart' === $command || 'start' === $command || 'restart' === $command ) {
			$ip = $this->getDockerGatewayIP();

			if ( empty( $ip ) ) {
				throw new Sq1Exception( 'Unable to get the Docker Gateway IP Address.' );
			}

			$result = putenv( sprintf( '%s=%s', self::VAR, $ip ) );

			if ( empty( $result ) ) {
				throw new Sq1Exception( sprintf( 'Unable to set %s environment variable.', self::VAR ) );
			}
		}
	}

	/**
	 * Get the docker gateway IP address
	 *
	 * @return string The IP Address
	 *
	 * @throws \Tribe\Sq1\Exceptions\Sq1Exception
	 */
	protected function getDockerGatewayIP(): ?string {
		$process = new Process( [ 'docker', 'network', 'inspect', 'bridge' ] );
		$process->run();

		if ( ! $process->isSuccessful() ) {
			throw new Sq1Exception( 'Unable to execute "docker network inspect bridge". Is docker installed?' );
		}

		$data = json_decode( $process->getOutput(), true );

		$ip = $data[0]['IPAM']['Config'][0]['Gateway'] ?? '';

		return filter_var( $ip, FILTER_VALIDATE_IP ) ?: null;
	}

}
