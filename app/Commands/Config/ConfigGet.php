<?php declare( strict_types=1 );

namespace App\Commands\Config;

use Filebase\Database;
use App\Commands\BaseCommand;
use App\Services\Docker\Local\Config;

/**
 * Get user config.
 *
 * @TODO    Add validation.
 *
 * @package App\Commands\Config
 */
class ConfigGet extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config:get {key : The config key}
                           {--g|global : Get global config}
                           {--s|secret : Get secret config}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get a configuration value';

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

        $value = $this->getConfig( $database, $key );

        if ( empty( $value ) ) {
            $this->error( sprintf( 'Config key "%s" was not found', $key ) );

            return self::EXIT_ERROR;
        }

        $this->outputConfig( $key, $value );

        return self::EXIT_SUCCESS;
    }

    /**
     * Get a config value.
     *
     * @param  string  $database  The name of the Filebase file without extension.
     * @param  string  $key       The config key.
     *
     * @return mixed
     */
    protected function getConfig( string $database, string $key ) {
        return $this->settings->get( $database )->field( $key );
    }

    /**
     * Output the config key : value.
     *
     * @param  string  $key
     * @param          $value
     */
    protected function outputConfig( string $key, $value ): void {
        $this->line( "<comment>[$key]</comment> <info>$value</info>" );
    }

}
