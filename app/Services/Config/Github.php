<?php declare(strict_types=1);

namespace App\Services\Config;

use Illuminate\Filesystem\Filesystem;

/**
 * Manages GitHub global tokens
 *
 * @package App\Services\Config
 */
class Github extends BaseConfig {

	/**
	 * Name & relative path of default composer auth.json.
	 */
	public const AUTH_FILE = '/auth.json';

	/**
	 * The default composer auth.json
	 */
	protected string $authFile;

	/**
	 * Github constructor.
	 *
	 * @param \Illuminate\Filesystem\Filesystem $filesystem
	 * @param  string      $directory
	 */
	public function __construct( Filesystem $filesystem, string $directory ) {
		parent::__construct( $filesystem, $directory );
		$this->authFile = $this->directory . self::AUTH_FILE;
	}

	/**
	 * Get the content of the auth file.
	 *
	 * @return string
	 *
	 * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
	 */
	public function get(): string {
		return $this->filesystem->get( $this->authFile );
	}

	/**
	 * If the default auth file exists.
	 *
	 * @return bool
	 */
	public function exists(): bool {
		return $this->filesystem->exists( $this->authFile );
	}

	/**
	 * Save the default auth.json.
	 *
	 * @param  string  $token  The GitHub oAuth token.
	 *
	 * @return bool
	 */
	public function save( string $token ): bool {
		return (bool) $this->filesystem->put( $this->authFile, sprintf( '{ "github-oauth": { "github.com": "%s" } }', trim( $token ) ) );
	}

	/**
	 * Copy the default auth.json to the local project's composer volume.
	 *
	 * @param  string  $composerVolume  The path to the composer volume.
	 *
	 * @return bool
	 */
	public function copy( string $composerVolume ): bool {
		return $this->filesystem->copy( $this->authFile, $composerVolume . self::AUTH_FILE );
	}

}
