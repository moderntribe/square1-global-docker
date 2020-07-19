<?php declare( strict_types=1 );

namespace App\Commands\Self;

use App\Services\Phar;
use RuntimeException;
use App\Services\Update\Updater;
use LaravelZero\Framework\Commands\Command;

/**
 * Class SelfUpdate
 *
 * @package App\Commands\Self
 */
class SelfUpdate extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'self:update';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Updates the application, if available';

    /**
     * The path to the currently running phar file.
     *
     * @var string
     */
    protected $installedPhar;

    /**
     * The application's name.
     *
     * @var string
     */
    protected $appName;

    /**
     * SelfUpdate constructor.
     *
     * @param  string  $installedPhar
     * @param  string  $appName
     */
    public function __construct( string $installedPhar, string $appName ) {
        parent::__construct();

        $this->installedPhar = $installedPhar;
        $this->appName       = $appName;
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Update\Updater  $updater
     * @param  \App\Services\Phar            $phar
     *
     * @return void
     * @throws \Exception
     */
    public function handle( Updater $updater, Phar $phar ): void {
        if ( empty( $phar->isPhar() ) ) {
            throw new RuntimeException( $this->name . ' only works when running the phar version of ' . $this->appName );
        }

        $release = $updater->getLatestReleaseFromGitHub();

        $this->info( sprintf( 'Updating %s to %s...', $this->appName, $release->version ) );

        $updater->update( $release, $this->installedPhar, $this );
    }

}
