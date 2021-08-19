<?php declare( strict_types=1 );

namespace App\Services\Migrations;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Manages the state of when migrations should run.
 */
class MigrationChecker {

    public const MIGRATION_FILE = '.migrated';

    /**
     * @var \Symfony\Component\Filesystem\Filesystem;
     */
    protected $filesystem;

    /**
     * The full path to the migration file. If this file does not exist,
     * we need to run a migration check.
     *
     * @var string
     */
    protected $migrationFile;

    /**
     * @param  string  $configDir  The path to the SquareOne configuration directory.
     */
    public function __construct( Filesystem $filesystem, string $configDir ) {
        $this->filesystem    = $filesystem;
        $this->migrationFile = sprintf( '%s/%s', $configDir, self::MIGRATION_FILE );
    }

    /**
     * Whether a migration should take place.
     *
     * @return bool
     */
    public function shouldMigrate(): bool {
        return ! $this->filesystem->exists( $this->migrationFile );
    }

    /**
     * Clear the migration file.
     */
    public function clear(): void {
        $this->filesystem->remove( $this->migrationFile );
    }

    /**
     * Create the migration file.
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function update(): void {
        $this->filesystem->touch( $this->migrationFile );
    }

}
