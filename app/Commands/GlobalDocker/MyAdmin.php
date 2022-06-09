<?php declare(strict_types=1);

namespace App\Commands\GlobalDocker;

use App\Commands\Open;
use App\Contracts\Runner;
use App\Commands\BaseCommand;
use Illuminate\Support\Facades\Artisan;

/**
 * phpMyAdmin Command
 *
 * @package App\Commands\GlobalDocker
 */
class MyAdmin extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'global:myadmin';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Starts a phpMyAdmin container';

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner  $runner
     *
     * @return void
     */
    public function handle( Runner $runner ): void {

        $this->info( '➜ Starting phpMyAdmin...' );

        $start = $runner->run( 'docker start tribe-phpmyadmin' );

        if ( ! $start->ok() ) {
            $run = $runner->tty( true )->run( $this->getRunCommand() );

            if ( ! $run->ok() ) {
                $runner->run( 'docker rm /tribe-phpmyadmin' );
                $runner->run( $this->getRunCommand() )->throw();
            }
        }

        Artisan::call( Open::class, [
            'url' => 'http://localhost:8080/',
        ] );
    }

    /**
     * Get the phpmyadmin docker run command.
     *
     * @return string
     */
    protected function getRunCommand(): string {
        return 'docker run -d --name tribe-phpmyadmin --link tribe-mysql:db --network="global_proxy" -p 8080:80 phpmyadmin';
    }

}
