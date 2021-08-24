<?php declare( strict_types=1 );

namespace App\Commands\Self;

use App\Services\Config\Github;
use Carbon\Carbon;
use Composer\Semver\Comparator;
use App\Services\Update\Updater;
use LaravelZero\Framework\Commands\Command;

/**
 * Update Check Command
 *
 * @package App\Commands
 */
class UpdateCheck extends Command {

    public const TIME_BETWEEN_CHECKS = '2 days';
    public const RELEASES_URL        = 'https://github.com/moderntribe/square1-global-docker/releases/tag/%s';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'self:update-check {--force    : Force an uncached check}
                                              {--only-new : Only show a notice if an update is available}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Check if there is an updated release';

    /**
     * The running application's version.
     *
     * @var string
     */
    protected $version;

    /**
     * UpdateCheck constructor.
     *
     * @param  string  $version
     */
    public function __construct( string $version ) {
        parent::__construct();

        $this->version = $version;
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Update\Updater  $updater  Manages updates.
     * @param  \App\Services\Config\Github   $github   Manages GitHub tokens.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle( Updater $updater, Github $github ): void {
        if ( $this->option( 'force' ) ) {
            $release = $updater->getLatestReleaseFromGitHub();
        } else {
            $release = $updater->getCachedRelease();
        }

        if ( empty( $release->version ) || (int) $release->updatedAt( 'U' ) < strtotime( '-' . self::TIME_BETWEEN_CHECKS ) ) {
            $release = $updater->getLatestReleaseFromGitHub();

            if ( empty( $release ) ) {
                // Try to use the global token
                if ( $github->exists() ) {
                    $json  = json_decode( $github->get(), true );
                    $token = $json['github-oauth']['github.com'] ?? '';
                }

                if ( empty( $token ) ) {
                    $this->error( 'Unable to fetch update data from the GitHub API' );
                    $token = $this->ask( 'Enter your GitHub token to try an authenticated request' );
                }

                $release = $updater->getLatestReleaseFromGitHub( $token );

                if ( empty( $release ) ) {
                    $this->error( 'An error occurred while checking for updates' );

                    return;
                }
            }
        }

        $shouldUpdate = Comparator::greaterThan( $release->version, $this->version );

        if ( $shouldUpdate ) {
            $this->question( sprintf(
                    'A new version "%s" is available! run "so self:update" to update now. See what\'s new: %s',
                    $release->version,
                    sprintf( self::RELEASES_URL, $release->version ),
                )
            );

            if ( ! $this->option( 'force' ) ) {
                $this->info( 'Note: this check is cached. Run "so self:update-check --force" to see if a newer version is available' );
                $this->info( sprintf(
                    'Cache last updated: %s',
                    Carbon::createFromDate( $release->updatedAt() )->diffForHumans()
                ) );
            }
        } elseif ( ! $this->option( 'only-new' ) ) {
            $this->info( sprintf( "You're running the latest version: %s", $this->version ) );
        }
    }

}
