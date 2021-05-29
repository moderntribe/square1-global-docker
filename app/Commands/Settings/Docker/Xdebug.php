<?php declare( strict_types=1 );

namespace App\Commands\Settings\Docker;

use App\Commands\BaseCommand;
use App\Services\Settings\Groups\AllSettings;

/**
 * Docker Xdebug settings.
 *
 * @package App\Commands\Settings\Docker
 */
class Xdebug extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'settings:docker:xdebug';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Enable/disable your xdebug preference globally';

    protected AllSettings $settings;

    public function __construct( AllSettings $settings ) {
        parent::__construct();

        $this->settings = $settings;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int {
        $current = $this->settings->docker->xdebug;

        $this->info( 'Set the default Xdebug state for all projects. Recommended: Off for macOS due to performance reasons' );

        $choice = $this->choice( 'Xdebug', [
            1 => 'On',
            0 => 'Off',
        ], $current );

        $this->settings->docker->xdebug = 'On' === $choice;
        $this->settings->save();

        return self::EXIT_SUCCESS;
    }

}
