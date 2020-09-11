<?php declare( strict_types=1 );

namespace App\Commands\GlobalDocker;

use App\Contracts\Runner;
use App\Commands\BaseCommand;

/**
 * Global docker status command
 *
 * @package App\Commands\GlobalDocker
 */
class Status extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'global:status';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Shows all running docker containers';

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner  $runner
     *
     * @return void
     */
    public function handle( Runner $runner ): void {

        $runner->output( $this )
               ->run( 'docker ps' )
               ->throw();

        $this->info( 'Done.' );
    }

}
