<?php declare( strict_types=1 );

namespace App\Commands\Config;

use Filebase\Database;
use App\Commands\BaseCommand;
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
     * @var \Filebase\Database
     */
    protected $settings;

    /**
     * Share constructor.
     *
     * @param  \Filebase\Database  $settings
     */
    public function __construct( Database $settings ) {
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
            $database = 'user_secrets';
        }

        if ( $this->option( 'global' ) ) {
            $database = 'global';
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
