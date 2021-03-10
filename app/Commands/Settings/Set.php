<?php declare( strict_types=1 );

namespace App\Commands\Settings;

use Flintstone\Flintstone;
use App\Commands\BaseCommand;

/**
 * The Settings:set Command.
 *
 * @package App\Commands\Settings
 */
class Set extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'settings:set {key   : The name of the setting}
                                         {value : The value of the setting}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Set the value of a setting';

    /**
     * Execute the console command.
     *
     * @param  \Flintstone\Flintstone  $settings
     *
     * @return int
     */
    public function handle( Flintstone $settings ): int {
        $settings->set( $this->argument( 'key' ), $this->argument( 'value' ) );

        $data = $settings->get( $this->argument( 'key' ) );

        $this->info( sprintf( '"%s" saved with value of "%s"', $this->argument( 'key' ), $data ) );

        return self::EXIT_SUCCESS;
    }

}
