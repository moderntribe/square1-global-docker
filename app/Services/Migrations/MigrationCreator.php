<?php declare( strict_types=1 );

namespace App\Services\Migrations;

use stdClass;
use ReflectionClass;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

/**
 * Class MigrationCreator
 *
 * @package App\Services\Migrations
 */
class MigrationCreator {

    /**
     * Illuminate Filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * MigrationCreator constructor.
     *
     * @param  Filesystem  $filesystem
     */
    public function __construct( Filesystem $filesystem ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Get the path and content to create a migration file
     *
     * @param  string  $name  The snake_case'd name of the migration
     * @param  string  $path  The path to where migrations will be created
     *
     * @return object
     *
     * @throws RuntimeException
     * @throws \ReflectionException|\Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getMigrationData( string $name, string $path ): object {
        $this->checkForExistingMigration( $name, $path );

        $content = $this->populateStub( $name, $this->getStub() );
        $path    = $this->getPath( $name, $path );

        $migration          = new stdClass();
        $migration->path    = $path;
        $migration->content = $content;

        return $migration;
    }

    /**
     * Check if a migration exists.
     *
     * @codeCoverageIgnore
     *
     * @param  string  $name
     * @param  string  $path
     *
     * @throws RuntimeException
     *
     * @throws \ReflectionException|\Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function checkForExistingMigration( string $name, string $path ) {

        foreach ( $this->filesystem->glob( "$path/*.php" ) as $file ) {
            $this->filesystem->requireOnce( $file );
        }

        if ( class_exists( $className = $this->getClassName( $name ) ) ) {
            $class = new ReflectionClass( $className );

            throw new RuntimeException( "{$class->getFileName()} already exists as class '$className'" );
        }
    }

    /**
     * Get the stub content for migration files
     *
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getStub(): string {
        return $this->filesystem->get( $this->getStubPath() . '/migration.stub' );
    }

    /**
     * Get the path to our stubs
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function getStubPath(): string {
        return __DIR__ . '/stubs';
    }

    /**
     * Get the migration file path
     *
     * @codeCoverageIgnore
     *
     * @param  string  $name
     * @param  string  $path
     *
     * @return string
     */
    protected function getPath( string $name, string $path ): string {
        return $path . '/' . $this->getDatePrefix() . '_' . $name . '.php';
    }

    /**
     * Populate the stub file
     *
     * @param  string  $name
     * @param  string  $stub
     *
     * @return string|string[]
     */
    public function populateStub( string $name, string $stub ) {
        return str_replace( '{{ class }}', $this->getClassName( $name ), $stub );
    }

    /**
     * Get the class name of a migration name.
     *
     * @codeCoverageIgnore
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getClassName( string $name ): string {
        return Str::studly( $name );
    }

    /**
     * Get the date prefix for the migration.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected function getDatePrefix(): string {
        return date( 'Y_m_d_His' );
    }

}
