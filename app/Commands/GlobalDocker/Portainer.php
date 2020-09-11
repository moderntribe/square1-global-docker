<?php declare( strict_types=1 );

namespace App\Commands\GlobalDocker;

use App\Commands\Open;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

/**
 * Opens Portainer Docker Management
 *
 * @package App\Commands
 */
class Portainer extends Command {

    public const PORTAINER_URL = 'http://portainer.tribe';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'global:portainer';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Launches Portainer docker management';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void {
        $this->info( sprintf( '➜ Launching Portainer at %s', self::PORTAINER_URL ) );

        Artisan::call( Open::class, [
            'url' => self::PORTAINER_URL,
        ] );
    }

}
