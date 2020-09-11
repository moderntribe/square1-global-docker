<?php declare( strict_types=1 );

namespace App\Commands\Config;

use Illuminate\Support\Facades\Http;
use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

/**
 * Download the squareone.yml file for customization.
 *
 * @package App\Commands\Config
 */
class ConfigCopy extends Command {

    /**
     * The path to the SquareOne configuration directory.
     *
     * @var string
     */
    protected $configDir;

    /**
     * The URL to the squareone.yml file in the GitHub repo.
     *
     * @var string
     */
    protected $downloadUrl;

    /**
     * ConfigCopy constructor.
     *
     * @param  string  $configDir    The path to the SquareOne configuration directory.
     * @param  string  $downloadUrl  The URL to the squareone.yml file in the GitHub repo.
     */
    public function __construct( string $configDir, string $downloadUrl ) {
        parent::__construct();

        $this->configDir   = $configDir;
        $this->downloadUrl = $downloadUrl;
    }

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config:copy';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Copies the squareone.yml file to the local config folder for customization';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     *
     * @return void
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handle( Filesystem $filesystem ): void {
        $this->info( '➜ Fetching config file...' );

        $response = Http::get( $this->downloadUrl )->throw();

        $filesystem->replace( $this->configDir . '/squareone.yml', $response->body() );

        $this->info( sprintf( '➜ Saved squareone.yml to %s', $this->configDir ) );

    }

}
