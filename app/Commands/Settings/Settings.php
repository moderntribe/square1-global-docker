<?php declare( strict_types=1 );

namespace App\Commands\Settings;

use Flintstone\Flintstone;
use App\Commands\BaseCommand;

/**
 * The Settings Command.
 *
 * @package App\Commands\Settings
 */
class Settings extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'settings';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Displays your current settings';

    /**
     * Execute the console command.
     *
     * @param  \Flintstone\Flintstone  $settings
     *
     * @return int
     */
    public function handle( Flintstone $settings ): int {
        $data = $settings->getAll();

        if ( empty( $data ) ) {
            $this->warn( 'No settings found!' );
        } else {
            $this->info( json_encode( $data, JSON_PRETTY_PRINT ) );
        }

        return self::EXIT_SUCCESS;
    }

}
