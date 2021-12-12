<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\CustomCommands\CommandCollection;
use App\Services\CustomCommands\CommandFactory;
use App\Services\CustomCommands\CommandLoader;
use App\Services\CustomCommands\Runner\RunnerCollection;
use App\Services\CustomCommands\Runner\Runners\HostRunner;
use App\Services\CustomCommands\Runner\Runners\MultiRunner;
use App\Services\CustomCommands\Runner\Runners\ServiceRunner;
use Illuminate\Support\ServiceProvider;

/**
 * Custom command service.
 */
class CustomCommandsServiceProvider extends ServiceProvider {

    public function register(): void {
        $this->app->bind( RunnerCollection::class, function () {
            return new RunnerCollection( [
                RunnerCollection::SERVICE_MULTI => $this->app->get( MultiRunner::class ),
                RunnerCollection::SERVICE       => $this->app->get( ServiceRunner::class ),
                RunnerCollection::HOST          => $this->app->get( HostRunner::class ),
            ] );
        } );

        $this->app->when( CommandFactory::class )
                  ->needs( '$commands' )
                  ->give( config( 'squareone.commands', [] ) );

        $this->app->when( CommandLoader::class )
                  ->needs( CommandCollection::class )
                  ->give( function () {
                      return $this->app->get( CommandFactory::class )->make();
                  } );

        // Register custom commands
        $this->app->make( CommandLoader::class )->register();
    }

}
