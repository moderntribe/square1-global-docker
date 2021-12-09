<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\Docker;
use App\Services\Docker\Container;
use App\Services\Docker\Local\Config;
use Illuminate\Support\Facades\Artisan;

/**
 * Local docker shell command
 *
 * @package App\Commands\LocalDocker
 */
class Shell extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'shell {--user=squareone : The username or UID of the account to use}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Gives you a bash shell into the php-fpm docker container';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Services\Docker\Container     $container
     *
     * @return void
     */
    public function handle( Config $config, Container $container ): void {
        $this->info( sprintf( '➜ Launching shell for %s...', $config->getProjectName() ) );

        $result = Artisan::call( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--user',
            $this->option( 'user' ),
            $container->getId(),
            '/bin/bash',
        ] );

        if ( self::EXIT_ERROR === $result ) {
            $this->error( 'Whoops! This project is using an older php-fpm container. Try running "so shell --user root" instead' );
        }
    }

}
