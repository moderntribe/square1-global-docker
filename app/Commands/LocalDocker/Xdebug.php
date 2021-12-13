<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\Docker;
use App\Services\Docker\Container;
use Illuminate\Support\Facades\Artisan;

/**
 * Enable/Disable Xdebug in the php-fpm container.
 *
 * @package App\Commands\LocalDocker
 */
class Xdebug extends BaseLocalDocker {

    /**
     * The path to the xdebug.ini in the container.
     */
    public const XDEBUG_CONFIG_PATH = '/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'xdebug {action? : on|off}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Enable/disable Xdebug in the php-fpm container to increase performance on MacOS';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Container  $container
     *
     * @return int
     */
    public function handle( Container $container ): int {
        $action      = $this->argument( 'action' );
        $containerId = $container->getId();

        if ( empty( $action ) ) {
            $result = Artisan::call( Docker::class, [
                'exec',
                '--tty',
                '--user',
                'root',
                $containerId,
                'bash',
                '-c',
                sprintf( '[[ -f %s ]]', self::XDEBUG_CONFIG_PATH ),
            ] );

            if ( self::EXIT_SUCCESS === $result ) {
                $this->info( 'xdebug is on' );
            } else {
                $this->info( 'xdebug is off' );
            }

            return self::EXIT_SUCCESS;
        }

        if ( 'on' === $action ) {
            $this->enable( $containerId );
            $this->reload( $containerId );
            $this->info( 'xdebug enabled' );
        } elseif ( 'off' === $action ) {
            $this->disable( $containerId );
            $this->reload( $containerId );
            $this->info( 'xdebug disabled' );
        } else {
            $this->error( sprintf( 'Invalid argument: %s. Allowed values: on|off', $action ) );
        }

        return self::EXIT_SUCCESS;
    }

    /**
     * Enable xdebug by renaming the .ini file.
     *
     * @param  string  $containerId
     */
    protected function enable( string $containerId ): void {
        Artisan::call( Docker::class, [
            'exec',
            '--user',
            'root',
            $containerId,
            'mv',
            sprintf( '%s', self::XDEBUG_CONFIG_PATH . '.disabled' ),
            sprintf( '%s', self::XDEBUG_CONFIG_PATH ),
        ] );
    }

    /**
     * Disable xdebug by renaming the .ini file back.
     *
     * @param  string  $containerId
     */
    protected function disable( string $containerId ): void {
        Artisan::call( Docker::class, [
            'exec',
            '--user',
            'root',
            $containerId,
            'mv',
            sprintf( '%s', self::XDEBUG_CONFIG_PATH ),
            sprintf( '%s', self::XDEBUG_CONFIG_PATH . '.disabled' ),
        ] );
    }

    /**
     * Reload PHP in the container.
     *
     * @param  string  $containerId
     */
    protected function reload( string $containerId ): void {
        Artisan::call( Docker::class, [
            'exec',
            '--user',
            'root',
            $containerId,
            'kill',
            '-USR2',
            '1',
        ] );
    }

}
