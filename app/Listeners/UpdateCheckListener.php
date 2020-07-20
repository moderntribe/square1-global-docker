<?php declare( strict_types=1 );

namespace App\Listeners;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Events\CommandFinished;

/**
 * Check for updates after a command has run.
 *
 * @package App\Listeners
 */
class UpdateCheckListener {

    /**
     * Run the update checker.
     *
     * @param  CommandFinished  $event
     *
     * @return bool
     */
    public function handle( CommandFinished $event ): bool {
        if ( $this->shouldRun( $event->command ) ) {
            $this->runUpdate( $event );

            return true;
        }

        return false;
    }

    /**
     * Check if we should run the update check.
     *
     * @param  string|null  $command
     *
     * @return bool
     */
    protected function shouldRun( ?string $command = '' ): bool {
        // Don't run during tests unless specified with the ALLOW_UPDATE_CHECK environment variable.
        if ( 'testing' === env( 'APP_ENV' ) && '1' != env( 'ALLOW_UPDATE_CHECK' ) ) {
            return false;
        }

        if ( empty( $command ) ) {
            return false;
        }

        if ( 'self' !== substr( $command, 0, 4 ) && 'app' !== substr( $command, 0, 3 ) ) {
            return true;
        }

        return false;
    }

    /**
     * Run the update check command.
     *
     * @param  \Illuminate\Console\Events\CommandFinished  $event
     */
    protected function runUpdate( CommandFinished $event ): void {
        Artisan::call( \App\Commands\Self\UpdateCheck::class, [
            '--only-new' => true,
        ], $event->output );
    }

}
