<?php declare(strict_types=1);

namespace App\Services\Nfs;

use App\Contracts\Runner;

/**
 * Represents the nfsd binary for managing NFS servers.
 *
 * @package App\Services\Nfs
 */
class Nfsd {

	/**
	 * The command runner.
	 */
	protected Runner $runner;

	/**
	 * Nfsd constructor.
	 *
	 * @param  \App\Contracts\Runner  $runner
	 */
	public function __construct( Runner $runner ) {
		$this->runner = $runner;
	}

	/**
	 * Start nfsd.
	 *
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	public function start(): void {
		$this->runner->run( 'sudo nfsd start' )->throw();
	}

	/**
	 * Stop nfsd.
	 *
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	public function stop(): void {
		$this->runner->run( 'sudo nfsd stop' )->throw();
	}

	/**
	 * Restart nfsd.
	 *
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	public function restart(): void {
		$this->runner->run( 'sudo nfsd restart' )->throw();
	}

	/**
	 * Test a configuration file for validity.
	 *
	 * @param  string  $filePath
	 *
	 * @throws \Symfony\Component\Process\Exception\ProcessFailedException
	 */
	public function check( string $filePath ): void {
		$this->runner->with( [ 'path' => $filePath ] )
					 ->run( 'nfsd -F {{ $path }} checkexports' )
					 ->throw();
	}

}
