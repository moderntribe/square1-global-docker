<?php declare(strict_types=1);

namespace App;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Bootstrap the application: This is one of the first instances to run during command execution.
 *
 * @package App
 */
class Bootstrap {

	public const GLOBAL_DIR = 'global';

	/**
	 * The location of the configuration directory
	 *
	 * Default Path: ~/.config/squareone
	 */
	protected string $configDir;

	/**
	 * Symfony filesystem
	 */
	protected Filesystem $filesystem;

	/**
	 * Directories to be created if they don't exist.
	 *
	 * @var string[]
	 */
	protected array $directories = [
		'defaults',
		'store',
	];

	/**
	 * Bootstrap constructor.
	 *
	 * @param  string                                    globalDockerDir
	 * @param  \Symfony\Component\Filesystem\Filesystem  $filesystem
	 */
	public function __construct( string $configDir, Filesystem $filesystem ) {
		$this->configDir  = $configDir;
		$this->filesystem = $filesystem;
	}

	/**
	 * Run all pre flight checks.
	 */
	public function boot(): void {
		$this->maybeCopyGlobalConfig();
		$this->createDirectories();
	}

	/**
	 * Create required configuration directories.
	 */
	protected function createDirectories(): void {
		foreach ( $this->directories as $directory ) {
			$directory = $this->configDir . '/' . $directory;

			if ( $this->filesystem->exists( $directory ) ) {
				continue;
			}

			$this->filesystem->mkdir( $directory, 0755 );
		}
	}

	/**
	 * Copy the global docker configuration folder if it's not already on the user's system.
	 */
	protected function maybeCopyGlobalConfig(): void {
		if ( $this->filesystem->exists( $this->configDir . '/' . self::GLOBAL_DIR ) ) {
			return;
		}

		$this->copyStorage();
	}

	/**
	 * Copy global docker directory to the user's system.
	 */
	protected function copyStorage(): void {
		$global = storage_path( 'global/' );
		$this->filesystem->mirror( $global, $this->configDir . '/' . self::GLOBAL_DIR );
	}

}
