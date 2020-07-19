<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Exceptions\SystemExitException;
use App\Recorders\ResultRecorder;
use App\Services\Docker\Local\Config;
use Exception;
use Illuminate\Support\Facades\Artisan;

/**
 * Local docker migrate command
 *
 * @package App\Commands\LocalDocker
 */
class MigrateDomain extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'migrate-domain';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Migrate a recently imported remote database to your local; Automatically detects the domain name.';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Recorders\ResultRecorder      $recorder
     *
     * @return void
     *
     * @throws \App\Exceptions\SystemExitException
     */
    public function handle( Config $config, ResultRecorder $recorder ): void {
        Artisan::call( Wp::class, [
            'args'    => [
                'db',
                'prefix',
            ],
            '--notty' => true,
            '--quiet' => true,
        ] );

        $dbPrefix = trim( $recorder->first() );

        Artisan::call( Wp::class, [
            'args'    => [
                'db',
                'query',
                "SELECT option_value FROM ${dbPrefix}options WHERE option_name = 'siteurl'",
                '--skip-column-names',
            ],
            '--notty' => true,
            '--quiet' => true,
        ] );

        $domain       = trim( $recorder->offsetGet( 1 ) );
        $sourceDomain = parse_url( $domain, PHP_URL_HOST );

        if ( empty( $sourceDomain ) ) {
            throw new Exception( sprintf( 'Invalid siteurl found in options table: %s', $domain ) );
        }

        $targetDomain = $config->getProjectDomain();

        if ( $sourceDomain === $targetDomain ) {
            throw new Exception( sprintf( 'Error: Source and target domains match: %s.', $sourceDomain ) );
        }

        $confirm = $this->confirm( sprintf( 'Ready to search and replace "%s" to "%s" (This cannot be undone)?', $sourceDomain, $targetDomain ) );

        if ( ! $confirm ) {
            throw new SystemExitException( 'Cancelling...' );
        }

        // Replace site URL.
        Artisan::call( Wp::class, [
            'args' => [
                'db',
                'query',
                "UPDATE ${dbPrefix}options SET option_value = REPLACE( option_value, '${sourceDomain}', '${targetDomain}' ) WHERE option_name = 'siteurl'",
            ],
        ] );

        // Search replace all tables with prefix.
        Artisan::call( Wp::class, [
            'args' => [
                'search-replace',
                "${sourceDomain}",
                "${targetDomain}",
                '--all-tables-with-prefix',
                '--verbose',
            ],
        ] );

        // Flush the cache.
        Artisan::call( Wp::class, [
            'args' => [
                'cache',
                'flush',
            ],
        ] );

        $this->info( 'Done.' );
    }

}
