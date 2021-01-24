<?php declare( strict_types=1 );

namespace App\Commands\Config;

use Filebase\Database;
use App\Commands\BaseCommand;
use App\Services\Docker\Local\Config;

/**
 * Set user config.
 *
 * @TODO Add validation.
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
        $key   = $this->argument( 'key' );
        $value = $this->argument( 'value' );

        // Set secret config
        if ( $this->option( 'secret' ) ) {
            $result = $this->setConfig( 'user_secrets', $key, $value );

            if ( ! $result ) {
                $this->error( sprintf( 'Unable to save secret for %s', $key ) );

                return self::EXIT_ERROR;
            }

            $this->info( sprintf( 'Saved secret to %s', $key ) );

            return self::EXIT_SUCCESS;
        }

        // Set global config
        if ( $this->option( 'global' ) ) {
            $result = $this->setConfig( 'global', $key, $value );

            if ( ! $result ) {
                $this->error( sprintf( 'Unable to save "%s" to "%s"', $value, $key ) );

                return self::EXIT_ERROR;
            }

            $this->info( sprintf( 'Saved "%s" to "%s"', $value, $key ) );

            return self::EXIT_SUCCESS;
        }

        // Set project config for the user
        $project = $config->getProjectName();
        $result  = $this->setConfig( $project, $key, $value );

        if ( ! $result ) {
            $this->error( sprintf( 'Unable to save "%s" to "%s" for %s', $value, $key, $project ) );

            return self::EXIT_ERROR;
        }

        $this->info( sprintf( 'Saved "%s" to "%s" for %s', $value, $key, $project ) );

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
