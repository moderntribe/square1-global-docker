<?php declare( strict_types=1 );

namespace App\Commands\Settings;

use App\Commands\BaseCommand;
use Illuminate\Filesystem\Filesystem;
use App\Services\Settings\Groups\AllSettings;

/**
 * Open command
 *
 * @package App\Commands
 */
class Settings extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'settings
                            {setting? : The setting to change in dot.notation}
                            {--set= : If provided, a new value for the given key}
                            {--reset : Reset settings to the default}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Stuff';

    protected AllSettings $settings;

    public function __construct( AllSettings $settings ) {
        parent::__construct();

        $this->settings = $settings;
    }

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     *
     * @return int
     */
    public function handle( Filesystem $filesystem ): int {
        $key   = $this->argument( 'setting' );
        $value = $this->option( 'set' );
        $reset = $this->option( 'reset' );

        if ( $reset ) {
            $confirm = $this->confirm( 'Are you sure you want to reset your settings to the defaults?' );

            if ( ! $confirm ) {
                $this->info( 'Cancelled.' );

                return self::EXIT_SUCCESS;
            }

            $filesystem->delete( $this->settings->writer()->file() );

            $this->info( 'Settings reset' );

            return self::EXIT_SUCCESS;
        }

        if ( $key ) {
            $setting = $this->getSetting( $key );

            if ( ! $setting ) {
                $this->error( sprintf( '%s does not exist!', $key ) );

                return self::EXIT_ERROR;
            }

            if ( null === $value ) {
                $this->info( $setting );

                return self::EXIT_SUCCESS;
            }

            $this->saveSetting( $key, $value );
        }

        $this->info( json_encode( $this->settings, JSON_PRETTY_PRINT ) );

        return self::EXIT_SUCCESS;
    }

    protected function getSetting( string $path ) {
        $properties = explode( '.', $path );

        $value = $this->settings;

        foreach ( $properties as $property ) {
            if ( isset( $value->$property ) ) {
                $value = $value->$property;
                continue;
            }

            return null;
        }

        return json_encode( $value, JSON_PRETTY_PRINT );
    }

    protected function saveSetting( string $path, $value ) {
        $properties = explode( '.', $path );

        foreach ( $properties as $property ) {
            if ( isset( $this->settings->$property ) ) {
                $p = next( $properties );

                if ( ! $p ) {
                    $this->settings->$property = $value;
                    continue;
                }

                $this->settings->$property->$p = $value;
                continue;
            }

            return;
        }

        $this->settings->save();
    }

}
