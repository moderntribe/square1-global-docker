<?php declare( strict_types=1 );

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Class EventServiceProvider
 *
 * @codeCoverageIgnore
 *
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider {

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Console\Events\CommandFinished' => [
	        'App\Listeners\UpdateCheckListener',
        ],
        'Illuminate\Console\Events\CommandStarting' => [
            'App\Listeners\MigrationListener',
        ],
    ];

}
