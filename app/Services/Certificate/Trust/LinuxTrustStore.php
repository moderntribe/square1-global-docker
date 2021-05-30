<?php declare(strict_types=1);

namespace App\Services\Certificate\Trust;

/**
 * Configure trust stores for different Linux flavors.
 *
 * @package App\Services\Certificate\Trust
 */
class LinuxTrustStore {

	/**
	 * The directory where CA certificates are stored.
	 */
	protected string $directory;

	/**
	 * The full path to where the CA will be stored using %s.
	 */
	protected string $filename;

	/**
	 * The command to run to trust a new CA certificate.
	 */
	protected string $command;

	/**
	 * LinuxTrustStore constructor.
	 *
	 * @param  string  $directory
	 * @param  string  $filename
	 * @param  string  $command
	 */
	public function __construct( string $directory, string $filename, string $command ) {
		$this->directory = $directory;
		$this->filename  = $filename;
		$this->command   = $command;
	}

	/**
	 * Get the directory where CA certificates are installed on the host.
	 *
	 * @return string
	 */
	public function directory(): string {
		return $this->directory;
	}

	/**
	 * Get the command to run to trust CA certificates.
	 *
	 * @return string
	 */
	public function command(): string {
		return $this->command;
	}

	/**
	 * Get the full path/filename to where the certificate is/would be stored.
	 *
	 * @note these include replacement strings e.g. %s.pem.
	 *
	 * @return string
	 */
	public function filename(): string {
		return $this->filename;
	}

}
