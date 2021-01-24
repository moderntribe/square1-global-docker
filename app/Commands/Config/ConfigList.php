<?php declare( strict_types=1 );

namespace App\Commands\Config;

use App\Commands\BaseCommand;
use App\Databases\ConfigDatabase;
use App\Services\Docker\Local\Config;

/**
 * List a user's config.
 *
 * @package App\Commands\Config
 */
class ConfigList extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config:list {--g|global : List global config}
                           {--s|secret : List secrets}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Lists config values or global config with -g';

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
        if ( $this->option( 'secret' ) ) {
            $database = ConfigDatabase::SECRETS;
        }

        if ( $this->option( 'global' ) ) {
            $database = ConfigDatabase::GLOBAL;
        }

        if ( empty( $database ) ) {
            $database = $config->getProjectName();
        }

        $settings = $this->settings->get( $database )->toArray();

        if ( empty( $settings ) ) {
            $this->error( sprintf( 'No configuration found for "%s"', $database ) );

            return self::EXIT_ERROR;
        }

        $this->line( $this->buildConfigOutput( $settings ) );

        return self::EXIT_SUCCESS;
    }

    /**
     * Takes a key=value array and builds a formatted list for output.
     *
     * @param  array  $list  The list to format.
     *
     * @return string The formatted list.
     */
    protected function buildConfigOutput( array $list ): string {
        $output = '';

        foreach ( $list as $config => $value ) {
            $output .= "<comment>[$config]</comment> <info>$value</info>";

            if ( array_key_last( $list ) !== $config ) {
                $output .= PHP_EOL;
            }
        }

        return $output;
    }

}
