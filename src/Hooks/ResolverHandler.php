<?php declare( strict_types=1 );

namespace Tribe\SquareOne\Hooks;

use Robo\Robo;
use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;
use Tribe\SquareOne\Exceptions\SquareOneException;
use Tribe\SquareOne\Models\OperatingSystem;

/**
 * Resolver/nameserver Hooks
 *
 * @package Tribe\SquareOne\Hooks
 */
class ResolverHandler extends Hook {

	/**
	 * The path to where the resolver config file will be written
	 *
	 * @var string
	 */
	protected $dir;

	/**
	 * The name of the file that will be written to
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Run via inflection
	 *
	 * @throws \Tribe\SquareOne\Exceptions\SquareOneException
	 */
	public function setDependencies(): void {
		if ( OperatingSystem::LINUX === $this->os->getFamily() ) {
			$resolverConfig = Robo::config()->get( sprintf( 'resolver.%s.%s', $this->os->getFamily(), $this->os->getLinuxFlavor() ) );
		} else {
			// MAC OS
			$resolverConfig = Robo::config()->get( sprintf( 'resolver.%s', $this->os->getFamily() ) );
		}

		if ( empty( $resolverConfig ) ) {
			throw new SquareOneException( 'Unsupported operating system' );
		}

		$this->dir  = $resolverConfig['dir'];
		$this->file = $resolverConfig['file'];
	}

	/**
	 * Configure the Resolver
	 *
	 * @hook init *
	 *
	 * @param  \Symfony\Component\Console\Input\InputInterface  $input
	 * @param  \Consolidation\AnnotatedCommand\AnnotationData   $data
	 *
	 * @throws \Tribe\SquareOne\Exceptions\SquareOneException
	 */
	public function configure( InputInterface $input, AnnotationData $data ): void {
		$command = $data->get( 'command' );

		if ( 'global:start' === $command || 'start' === $command ) {
			$file = $this->dir . $this->file;

			if ( ! file_exists( $file ) ) {
				printf( 'Writing nameservers to %s. Enter your sudo password. ', $file );
				$this->writeResolver();
			}
		}
	}

	/**
	 * Writes nameservers to a resolver file and copies it to the correct location
	 *
	 * @param  string  $nameserverIp  The nameserver IP to add to the file.
	 *
	 * @throws \Tribe\SquareOne\Exceptions\SquareOneException
	 */
	protected function writeResolver( string $nameserverIp = '127.0.0.1' ): void {
		$file = $this->dir . $this->file;

		$tmpFile = tempnam( '/tmp', 'sq1' );
		chmod( $tmpFile, 0644 );

		$result = file_put_contents( $tmpFile, sprintf( 'nameserver %s', $nameserverIp ) );

		if ( empty( $result ) ) {
			throw new SquareOneException( sprintf( 'Unable to save nameservers to %s', $tmpFile ) );
		}

		if ( ! is_dir( $this->dir ) ) {
			shell_exec( sprintf( 'sudo mkdir -p %s', $this->dir ) );
		}

		shell_exec( sprintf( 'sudo cp %s %s', $tmpFile, $file ) );

		unset( $tmpFile );
	}


}
