<?php declare( strict_types=1 );

namespace Tribe\Sq1\Hooks;

use Robo\Robo;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;
use Tribe\Sq1\Commands\SquareOneCommand;
use Tribe\Sq1\Models\Certificate;
use Tribe\Sq1\Models\LocalDocker;
use Tribe\Sq1\Models\OperatingSystem;
use Tribe\Sq1\Traits\LocalAwareTrait;

/**
 * CertificateHandler Hooks
 *
 * @package Tribe\Sq1\Hooks
 */
class CertificateHandler {

	use LocalAwareTrait;

	public const CERT_TARGET_NAME = 'tribeCA.crt';

	/**
	 * @var OperatingSystem
	 */
	protected $os;

	/**
	 * @var Certificate
	 */
	protected $localCertificate;

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
	 * @param  \Tribe\Sq1\Models\Certificate      $localCertificate
	 */
	public function init( OperatingSystem $os, Certificate $localCertificate ): void {
		$this->os               = $os;
		$this->localCertificate = $localCertificate;

		if ( OperatingSystem::LINUX === $os->getFamily() ) {
			$config        = Robo::config()->get( sprintf( 'certificate.Linux.%s', $os->getLinuxFlavor() ) );
			$this->dir     = $config['dir'];
			$this->command = $config['command'];
		}
	}

	/**
	 * Generate and install our custom CA CertificateHandler
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

			printf( 'Writing CA CertificateHandler. Enter your sudo password when requested. ' );
			shell_exec( sprintf( 'sudo openssl x509 -outform der -in %s -out %s', $ca,
				$this->dir . self::CERT_TARGET_NAME ) );
			shell_exec( sprintf( 'sudo %s', $this->command ) );
		}
	}

	/**
	 * Create a SSL certificate for a local project if it doesn't exist.
	 *
	 * @hook init *
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Consolidation\AnnotatedCommand\AnnotationData   $data
	 */
	public function installLocalCertificate( InputInterface $input, AnnotationData $data ): void {
		$command = $data->get( 'command' );

		if ( 'start' === $command ) {
			$certPath = sprintf( '%s/%s.tribe.crt', Robo::config()->get( 'docker.certs-folder' ), Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) );

			$cert = $this->localCertificate->setCertPath( $certPath );

			// Generate a certificate for this project if it doesn't exist or if it expired
			if ( ! $cert->exists() || $cert->expired() ) {
				shell_exec( sprintf( '%s %s.tribe', SquareOneCommand::SCRIPT_PATH . 'global/cert.sh',
					Robo::config()->get( LocalDocker::CONFIG_PROJECT_NAME ) ) );
			}
		}
	}

}
