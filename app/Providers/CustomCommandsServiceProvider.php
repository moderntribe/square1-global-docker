<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\CustomCommands\CommandCollection;
use App\Services\CustomCommands\CommandFactory;
use App\Services\CustomCommands\CommandLoader;
use App\Services\CustomCommands\Runners\HostCommandRunner;
use App\Services\CustomCommands\Runners\MultiCommandRunner;
use App\Services\CustomCommands\Runners\RunnerCollection;
use App\Services\CustomCommands\Runners\ServiceCommandRunner;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\ServiceProvider;

/**
 * Custom command service.
 */
class CustomCommandsServiceProvider extends ServiceProvider {

    public function register(): void {
        $this->app->bind(
            \Illuminate\Contracts\Pipeline\Pipeline::class,
            Pipeline::class
        );

        // The command runner pipes for the pipeline
        $this->app->bind( RunnerCollection::class, function () {
            return new RunnerCollection( [
                MultiCommandRunner::class,
                HostCommandRunner::class,
                ServiceCommandRunner::class,
            ] );
        } );

        // Custom commands from a project's squareone.yml
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
