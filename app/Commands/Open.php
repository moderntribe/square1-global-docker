<?php declare( strict_types=1 );

namespace App\Commands;

use App\Services\BrowserLauncher;

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
    protected $signature = 'open {url : The URL to open}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Open\'s a URL in your default browser';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\BrowserLauncher  $launcher
     *
     * @return void
     */
    public function handle( BrowserLauncher $launcher ): void {
        $launcher->open( $this->argument( 'url' ) );
    }

}
