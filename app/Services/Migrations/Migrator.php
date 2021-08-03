<?php declare( strict_types=1 );

namespace App\Services\Migrations;

use Filebase\Database;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class Migrator
 *
 * @package App\Services\Migrations
 */
class Migrator {

    /**
     * The migrations database
     *
     * @var Database
     */
    protected $db;

    /**
     * Illuminate Filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The laravel service container.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Migrator constructor.
     *
     * @param  Database                                      $db          The migrations database
     * @param  \Illuminate\Filesystem\Filesystem             $filesystem  Illuminate filesystem.
     * @param  \Illuminate\Contracts\Foundation\Application  $app         The laravel service container.
     */
    public function __construct( Database $db, Filesystem $filesystem, Application $app ) {
        $this->db         = $db;
        $this->filesystem = $filesystem;
        $this->app        = $app;
    }

    /**
     * Run migrations.
     *
     * @param  \Symfony\Component\Finder\Finder                   $migrations  The Finder iterator of Symfony\Component\Finder\SplFileInfo objects.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return \Illuminate\Support\Collection A collection of migration files that ran
     */
    public function run( Finder $migrations, OutputInterface $output ): Collection {
        $results = [];

        // Migrations need to be run in order.
        $migrations->sortByName( true );

        foreach ( $migrations as $migration ) {
            $filename = $migration->getFilename();
            $id       = $migration->getFilenameWithoutExtension();

            if ( ! $this->db->has( $id ) ) {
                $this->require( $migration->getPathname() );
                $instance = $this->resolve( $id );

                $result = new stdClass();

                $result->success   = false;
                $result->migration = $filename;

                if ( $instance->up( $output ) ) {
                    $this->store( $id );

                    $result->success = true;
                }

                $results[] = $result;
            }
        }

        return collect( $results );
    }

    /**
     * Require a migration file.
     *
     * @codeCoverageIgnore
     *
     * @param  string  $file  The full path to the migration file.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function require( string $file ): void {
        $this->filesystem->requireOnce( $file );
    }

    /**
     * Resolve a migration instance from a file.
     *
     * @codeCoverageIgnore
     *
     * @param  string  $filenameWithoutExtension
     *
     * @return Migration
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function resolve( string $filenameWithoutExtension ): Migration {
        $class = Str::studly( implode( '_', array_slice( explode( '_', $filenameWithoutExtension ), 4 ) ) );
        $class = '\\' . $class;

        return $this->app->make( $class );
    }

    /**
     * Store that a migration has run
     *
     * @codeCoverageIgnore
     *
     * @param  string  $id
     */
    protected function store( string $id ): void {
        $item = $this->db->get( $id );
        $item->save();
    }

}
