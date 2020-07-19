<?php declare( strict_types=1 );

namespace App\Commands\GlobalDocker;

use App\Commands\BaseCommand;
use App\Contracts\Runner;

/**
 * Global docker stop-all command
 *
 * @package App\Commands\GlobalDocker
 */
class StopAll extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'global:stop-all';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Stops all running docker containers';

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner  $runner
     *
     * @return void
     */
    public function handle( Runner $runner ): void {
        $this->info( '➜ Stopping all docker containers...' );

        $runner->output( $this )
                     ->run( 'docker stop $(docker ps -aq)' )
                     ->throw();

        $this->info( 'Done.' );
    }
}
