<?php declare( strict_types=1 );

namespace App\Commands\Config;

use Illuminate\Support\Facades\Http;
use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

/**
 * Download the global docker-compose.yml file for customization.
 *
 * @package App\Commands\Config
 */
class ComposeCopy extends Command {

    /**
     * The path where we'll save the docker-compose.override.yml file.
     *
     * @var string
     */
    protected $composeOverride;

    /**
     * The URL to the docker-compose.yml in the GitHub repo.
     *
     * @var string
     */
    protected $downloadUrl;

    /**
     * ComposeCopy constructor.
     *
     * @param  string  $composeOverride  The path where we'll save the docker-compose.override.yml file.
     * @param  string  $downloadUrl      The URL to the docker-compose.yml in the GitHub repo.
     */
    public function __construct( string $composeOverride, string $downloadUrl ) {
        parent::__construct();

        $this->composeOverride = $composeOverride;
        $this->downloadUrl     = $downloadUrl;
    }

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config:compose-copy';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Copies the Global docker-compose.yml file to the local config folder for customization';

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
        $this->info( '➜ Fetching docker-compose.yml...' );

        $response = Http::get( $this->downloadUrl )->throw();

        $filesystem->replace( $this->composeOverride, $response->body() );

        $this->info( sprintf( '➜ Saved to %s', $this->composeOverride ) );

    }

}
