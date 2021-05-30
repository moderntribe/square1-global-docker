<?php declare(strict_types=1);

namespace App\Services\Certificate\Trust;

use App\Contracts\Runner;
use App\Contracts\Trustable;
use Illuminate\Filesystem\Filesystem;

/**
 * Class BaseTrust
 *
 * @package App\Services\Certificate\Trust
 */
abstract class BaseTrust implements Trustable {

	protected Filesystem $filesystem;

	/**
	 * The command runner.
	 */
	protected Runner $runner;

	/**
	 * Whether the certificate is already installed on the host system.
	 *
	 * @return bool
	 */
	abstract public function installed(): bool;

	/**
	 * Run the commands in order to trust a CA certificate
	 *
	 * @param  string  $crt  The path to the crt file
	 *
	 * @return mixed
	 */
	abstract public function trustCa( string $crt );

	/**
	 * Run the commands in order to trust a certificate
	 *
	 * @param  string  $crt  The path to the crt file
	 *
	 * @return mixed
	 */
	abstract public function trustCertificate( string $crt );

	/**
	 * BaseTrust constructor.
	 *
	 * @param  \Illuminate\Filesystem\Filesystem  $filesystem
	 * @param  \App\Contracts\Runner              $runner
	 */
	public function __construct( Filesystem $filesystem, Runner $runner ) {
		$this->filesystem = $filesystem;
		$this->runner     = $runner;
	}

}
