<?php declare( strict_types=1 );

namespace App\Commands\Settings;

use Flintstone\Flintstone;
use App\Commands\BaseCommand;

/**
 * The Settings:get Command.
 *
 * @package App\Commands\Settings
 */
class Get extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'settings:get {key : The name of the setting}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Show the value of a setting';

    /**
     * Execute the console command.
     *
     * @param  \Flintstone\Flintstone  $settings
     *
     * @return int
     */
    public function handle( Flintstone $settings ): int {
        $data = $settings->get( $this->argument( 'key' ) );

        if ( empty( $data ) ) {
            $this->warn( sprintf( 'Unable to find a setting with the key of: %s', $this->argument( 'key' ) ) );
        } else {
            $this->info( $data );
        }

        return self::EXIT_SUCCESS;
    }

}
