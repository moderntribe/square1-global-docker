<?php declare( strict_types=1 );

namespace App\Commands\Config;

use App\Commands\BaseCommand;
use App\Databases\ConfigDatabase;
use App\Services\Docker\Local\Config;

/**
 * Set user config.
 *
 * @TODO    Add validation.
 *
 * @package App\Commands\Config
 */
class ConfigSet extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config:set {key : The config key} {value : The config value}
                           {--g|global : Apply command to global config}
                           {--s|secret : Apply command to secrets}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Set a configuration value';

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
        $key   = $this->argument( 'key' );
        $value = $this->argument( 'value' );

        if ( $this->option( 'secret' ) ) {
            $database = ConfigDatabase::SECRETS;
        }

        if ( $this->option( 'global' ) ) {
            $database = ConfigDatabase::GLOBAL;
        }

        if ( empty( $database ) ) {
            $database = $config->getProjectName();
        }

        $result  = $this->setConfig( $database, $key, $value );

        if ( ! $result ) {
            $this->error( sprintf( 'Unable to save "%s" to "%s" in %s', $value, $key, $database ) );

            return self::EXIT_ERROR;
        }

        $this->info( sprintf( 'Saved "%s" to "%s" for %s', $value, $key, $database ) );

        return self::EXIT_SUCCESS;
    }

    /**
     * Save a config value.
     *
     * @param  string  $database  The name of the Filebase file without extension.
     * @param  string  $key       The config key.
     * @param  string  $value     The config value.
     *
     * @return bool
     */
    protected function setConfig( string $database, string $key, string $value ): bool {
        $settings = $this->settings->get( $database );

        $settings->{$key} = $value;

        $settings->save();

        return (bool) $settings->save();
    }

}
