<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use Filebase\Database;
use App\Contracts\Runner;
use App\Services\Docker\Local\Config;
use Illuminate\Filesystem\Filesystem;

/**
 * Share your local environment using ngrok
 *
 * @package App\Commands\LocalDocker
 */
class Share extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'share';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Share your local project on a temporary URL using ngrok';

    /**
     * The user's settings database.
     *
     * @var \Filebase\Database
     */
    protected $settings;

    /**
     * Share constructor.
     *
     * @param  \Filebase\Database  $settings
     */
    public function __construct( Database $settings ) {
        parent::__construct();
        $this->settings = $settings;
    }

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Contracts\Runner              $runner
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     *
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle( Config $config, Runner $runner, Filesystem $filesystem ): int {
        $settings = $this->settings->get( 'user_secrets' );

        if ( empty( $settings->ngrok_token ) ) {
            $this->info( 'Ngrok requires a free user account to proxy to https domains. Sign up: https://dashboard.ngrok.com/signup' );
            $authToken = $this->secret( 'Enter your authtoken found in your dashboard: https://dashboard.ngrok.com/auth/your-authtoken (input hidden)' );

            if ( empty( $authToken ) ) {
                $this->error( 'No token entered' );

                return self::EXIT_ERROR;
            }

            $settings->ngrok_token = $authToken;
            $settings->save();
        }

        // TODO: perhaps add the plugin path to .gitignore or ask the user to do so?

        $source  = storage_path( 'wordpress/mu-plugins/0-so-ngrok-local.php' );
        $content = $filesystem->get( $source );
        $target  = sprintf( '%s/%s', $config->getProjectRoot(), 'wp-content/mu-plugins/0-so-ngrok-local.php' );

        $filesystem->replace( $target, $content );

        $runner->with( [
            'domain' => $config->getProjectDomain(),
            'token'  => $settings->ngrok_token,
        ] )->tty( true )
               ->run( 'docker run --rm -it --net global_proxy --link tribe-proxy wernight/ngrok ngrok http --authtoken {{ $token }} -host-header={{ $domain }} tribe-proxy:443' )
               ->throw();

        $filesystem->delete( $target );

        return self::EXIT_SUCCESS;
    }

}
