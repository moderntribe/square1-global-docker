<?php declare( strict_types=1 );

namespace App\Commands\GlobalDocker;

use InvalidArgumentException;
use App\Services\Certificate\Handler;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

/**
 * Generate a local SSL certificate for a particular domain.
 *
 * @package App\Commands\GlobalDocker
 */
class Cert extends Command {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'global:cert
                            {domain : The domain to create a certificate for}
                            {--wildcard : Allow *.tribe wildcard generation}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Manually generate a certificate for a local domain';


    /**
     * Execute the console command.
     *
     * @param  \App\Services\Certificate\Handler  $certificateHandler
     *
     * @return void
     */
    public function handle( Handler $certificateHandler ): void {
        $domain = $this->argument( 'domain' );

        if ( ! $this->option( 'wildcard' ) && ! $this->validateDomain( $domain ) ) {
            throw new InvalidArgumentException( 'Invalid domain provided' );
        }

        $this->task( '➜ Generating a certificate for ' . $domain, function () use ( $domain, $certificateHandler ) {
            $certificateHandler->createCertificate( $domain );
        } );

        $this->task( '➜ Restarting global docker', function () {
            return Artisan::call( Restart::class );
        } );
    }

    /**
     * Check if this is a valid domain
     *
     * @param  string  $domain The domain
     *
     * @return bool
     */
    protected function validateDomain( string $domain ): bool {
        return !!preg_match('/^(?:[a-z0-9](?:[a-z0-9-æøå]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/isu', $domain);
    }

}
