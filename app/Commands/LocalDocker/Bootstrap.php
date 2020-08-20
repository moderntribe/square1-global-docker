<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\Open;
use App\Services\Docker\Local\Config;
use App\Services\ProjectBootstrapper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

/**
 * Class Bootstrap
 *
 * @package App\Commands\LocalDocker
 */
class Bootstrap extends BaseLocalDocker {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'bootstrap';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Bootstrap WordPress: Install core, create an admin user';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     *
     * @param  \App\Services\ProjectBootstrapper  $bootstrapper
     *
     * @return int
     */
    public function handle( Config $config, ProjectBootstrapper $bootstrapper ) {
        $this->info( 'Alright, let\'s get ready to configure WordPress!' );

        $email                = $this->ask( 'Enter your email address' );
        $username             = $this->ask( 'Enter your admin username' );
        $password             = $this->secret( 'Enter your password' );
        $passwordConfirmation = $this->secret( 'Confirm your password' );

        $validator = Validator::make( [
            'email'                 => $email,
            'username'              => $username,
            'password'              => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'email'                 => [ 'required', 'email' ],
            'username'              => [ 'required' ],
            'password'              => [ 'required', 'same:password_confirmation' ],
            'password_confirmation' => [ 'required' ],
        ], [
            'required' => 'The :attribute field is required',
            'same'     => 'The :attribute and :other must match',
            'email'    => 'Invalid email address',
        ] );

        if ( $validator->fails() ) {

            foreach ( $validator->errors()->all() as $error ) {
                $this->error( $error );
            }

            return self::EXIT_ERROR;
        }

        $bootstrapper->renameObjectCache( $config->getProjectRoot() );

        $this->task( 'Bootstrapping project', call_user_func( [ $this, 'bootstrap' ], $config, $bootstrapper ) );
        $this->task( 'Starting docker containers', call_user_func( [ $this, 'startContainers' ] ) );
        $this->task( 'Installing WordPress', call_user_func( [ $this, 'installWordpress' ], $config, $email, $username, $password ) );

        $bootstrapper->restoreObjectCache( $config->getProjectRoot() );

        $this->info( sprintf( 'Done! Opening %s in your default browser', $config->getProjectUrl() ) );
        $this->warn( 'If you just created a project, don\'t forget to commit the changes!' );

        Artisan::call( Open::class, [
            'url' => $config->getProjectUrl(),
        ] );

        return self::EXIT_SUCCESS;
    }

    /**
     * Starts all required docker containers.
     *
     */
    public function startContainers(): void {
        Artisan::call( Start::class, [], $this->output );
    }

    /**
     * Bootstrap the project
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Services\ProjectBootstrapper  $bootstrapper
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function bootstrap( Config $config, ProjectBootstrapper $bootstrapper ): void {
        $projectRoot = $config->getProjectRoot();

        $bootstrapper->createDatabases( $config->getProjectName() );

        $result = $bootstrapper->createLocalConfig( $projectRoot );

        if ( ! $result ) {
            // @codeCoverageIgnoreStart
            $this->info( 'local-config.php already exists. Skipping...' );
            // @codeCoverageIgnoreEnd
        }

        $bootstrapper->createLocalConfigJson( $projectRoot, $config->getProjectDomain() )
                     ->buildFrontend( $projectRoot, $this->output );
    }

    /**
     * Install WordPress.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  string                             $email
     * @param  string                             $username
     * @param  string                             $password
     */
    public function installWordpress( Config $config, string $email, string $username, string $password ): void {
        Artisan::call( Wp::class, [
            'args' => [
                'core',
                'install',
                '--url'            => $config->getProjectDomain(),
                '--title'          => 'Square One',
                '--admin_email'    => $email,
                '--admin_user'     => $username,
                '--admin_password' => $password,
                '--skip-email',
            ],
        ] );

        Artisan::call( Wp::class, [
            'args' => [
                'rewrite',
                'structure',
                '/%postname%/',
            ],
        ] );
    }

}
