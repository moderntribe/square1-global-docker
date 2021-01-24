<?php declare( strict_types=1 );

namespace App\Commands\Config;

use Filebase\Database;
use App\Commands\BaseCommand;
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
                           {--s|secrets : List sensitive config options}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Lists config values or global config with -g';

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
        if ( $this->option( 'secrets' ) ) {
            $settings = $this->settings->get( 'user_secrets' )->toArray();

            if ( empty( $settings ) ) {
                $this->error( 'Secret configuration is empty' );

                return self::EXIT_ERROR;
            }

            $this->line( $this->buildConfigOutput( $settings ) );

            return self::EXIT_SUCCESS;
        }

        if ( $this->option( 'global' ) ) {
            $settings = $this->settings->get( 'global' )->toArray();

            if ( empty( $settings ) ) {
                $this->error( 'Global configuration is empty' );

                return self::EXIT_ERROR;
            }

            $this->line( $this->buildConfigOutput( $settings ) );

            return self::EXIT_SUCCESS;
        }

        $project = $config->getProjectName();

        $settings = $this->settings->get( $project )->toArray();

        if ( empty( $settings ) ) {
            $this->error( sprintf( 'No configuration found for %s', $project ) );

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
            $output .= "<comment>[$config]</comment> <info>$value</info>" . PHP_EOL;
        }

        return $output;
    }

}
