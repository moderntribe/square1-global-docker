<?php declare( strict_types=1 );

namespace App\Commands\Config;

use App\Commands\BaseCommand;
use App\Databases\ConfigDatabase;
use App\Services\Docker\Local\Config;

/**
 * Delete user config.
 *
 * @TODO    Add validation.
 *
 * @package App\Commands\Config
 */
class ConfigDelete extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config:delete {key : The config key}
                           {--g|global : Get global config}
                           {--s|secret : Get secret config}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete a configuration option';

    /**
     * The user's settings database.
     *
     * @var \App\Databases\ConfigDatabase
     */
    protected $settings;

    /**
     * Share constructor.
     *
     * @param  \App\Databases\ConfigDatabase  $settings
     */
    public function __construct( ConfigDatabase $settings ) {
        parent::__construct();
        $this->settings = $settings;
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     *
     * @return int
     */
    public function handle( Config $config ): int {
        $key = $this->argument( 'key' );

        if ( $this->option( 'secret' ) ) {
            $database = ConfigDatabase::SECRETS;
        }

        if ( $this->option( 'global' ) ) {
            $database = ConfigDatabase::GLOBAL;
        }

        if ( empty( $database ) ) {
            $database = $config->getProjectName();
        }

        $confirm = $this->confirm( sprintf( 'Are you sure you want to delete "%s" from "%s" ', $key, $database ) );

        if ( ! $confirm ) {
            $this->info( 'Aborted delete' );

            return self::EXIT_SUCCESS;
        }

        $result = $this->deleteConfig( $database, $key );

        if ( ! $result ) {
            $this->error( sprintf( 'Config key "%s" was not found in "%s"', $key, $database ) );

            return self::EXIT_ERROR;
        }

        $this->info( sprintf( 'Deleted "%s" from "%s"', $key, $database ) );

        return self::EXIT_SUCCESS;
    }

    /**
     * Delete a config key.
     *
     * @param  string  $database  The name of the Filebase file without extension.
     * @param  string  $key       The config key.
     *
     * @return mixed
     */
    protected function deleteConfig( string $database, string $key ): bool {
        $settings = $this->settings->get( $database );

        if ( isset( $settings->{$key} ) ) {
            unset( $settings->{$key} );

            return (bool) $settings->save();
        }

        return false;
    }

}
