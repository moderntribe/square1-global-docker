<?php declare( strict_types=1 );

namespace App\Commands\App;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;
use App\Services\Migrations\MigrationCreator;

/**
 * Create a new migration to be run when the user updates the application.
 *
 * @package App\Commands\App
 */
class CreateMigration extends Command {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:create-migration';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Creates a migration file for customization';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Migrations\MigrationCreator  $creator  The migration creator.
     * @param  \Illuminate\Filesystem\Filesystem          $filesystem
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \ReflectionException
     */
    public function handle( MigrationCreator $creator, Filesystem $filesystem ): void {
        $name = $this->ask( 'Enter the name of this migration' );

        $name = Str::snake( trim( $name ) );

        $migration = $creator->getMigrationData( $name, storage_path( 'migrations' ) );

        $filesystem->put( $migration->path, $migration->content );

        $this->info( sprintf( '➜ Migration file created at %s', $migration->path ) );
    }

}
