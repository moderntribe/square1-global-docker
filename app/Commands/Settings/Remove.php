<?php declare( strict_types=1 );

namespace App\Commands\Settings;

use Flintstone\Flintstone;
use App\Commands\BaseCommand;

/**
 * The Settings:remove Command.
 *
 * @package App\Commands\Settings
 */
class Remove extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'settings:remove {key   : The name of the setting}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove a setting';

    /**
     * Execute the console command.
     *
     * @param  \Flintstone\Flintstone  $settings
     *
     * @return int
     */
    public function handle( Flintstone $settings ): int {
        $settings->delete( $this->argument( 'key' ) );

        $this->info( sprintf( 'Removed setting: %s', $this->argument( 'key' ) ) );

        return self::EXIT_SUCCESS;
    }

}
