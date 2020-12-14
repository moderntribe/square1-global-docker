<?php declare( strict_types=1 );

namespace App\Commands;

use Exception;
use App\Services\BrowserLauncher;
use App\Services\Docker\Local\Config;

/**
 * Open command
 *
 * @package App\Commands
 */
class Open extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'open {url? : The URL to open}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Open\'s a URL in your default browser or the current SquareOne project';

    /**
     * Execute the console command.
     *
     * @param   \App\Services\BrowserLauncher  $launcher
     * @param   Config                         $config
     *
     * @return int
     */
    public function handle( BrowserLauncher $launcher, Config $config ): int {
        $url = $this->argument( 'url' );

        if ( empty( $url ) ) {
            try {
                $url = $config->getProjectUrl();
            } catch ( Exception $e ) {
                $this->error( 'Please provide a valid URL or ensure this is run inside a SquareOne project' );

                return self::EXIT_ERROR;
            }

        }

        $launcher->open( $url );

        return self::EXIT_SUCCESS;
    }

}
