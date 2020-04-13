<?php declare( strict_types=1 );

namespace Tribe\Sq1\Hooks;

use Robo\Robo;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;
use Tribe\Sq1\Models\OperatingSystem;

/**
 * Certificate Hooks
 *
 * @package Tribe\Sq1\Hooks
 */
class Certificate {

	public const CERT_TARGET_NAME = 'tribeCA.crt';

	/**
	 * @var OperatingSystem
	 */
	protected $os;

	/**
	 * The OS's location to store CA certificates
	 *
	 * @var string
	 */
	protected $dir = '';

	/**
	 * The command to run to update CA certificates
	 *
	 * @var string
	 */
	protected $command = '';

	/**
	 * Invoked via inflection
	 *
	 * @param  \Tribe\Sq1\Models\OperatingSystem  $os
	 */
	public function init( OperatingSystem $os ): void {
		if ( OperatingSystem::LINUX === $os->getFamily() ) {
			$config        = Robo::config()->get( sprintf( 'certificate.Linux.%s', $os->getLinuxFlavor() ) );
			$this->dir     = $config['dir'];
			$this->command = $config['command'];
		}
	}

	/**
	 * Generate and install our custom CA Certificate
	 *
	 * @hook init *
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Consolidation\AnnotatedCommand\AnnotationData   $data
	 */
	public function installCaCertificate( InputInterface $input, AnnotationData $data ): void {

		if ( empty( $this->dir ) || empty ( $this->command ) ) {
			return;
		}

		$destinationCert = $this->dir . self::CERT_TARGET_NAME;

		if ( ! file_exists( $destinationCert ) ) {
			$caCertName = Robo::config()->get( 'docker.cert-ca' );
			$ca         = Robo::config()->get( 'docker.certs-folder' ) . '/' . $caCertName;

			printf( 'Writing CA Certificate. Enter your sudo password when requested. ' );
			shell_exec( sprintf( 'sudo openssl x509 -outform der -in %s -out %s', $ca ,
				 $this->dir . self::CERT_TARGET_NAME  ) );
			shell_exec( sprintf( 'sudo %s', $this->command  ) );
		}
	}
}
