<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use Exception;
use M1\Env\Parser;
use App\Commands\Open;
use App\Contracts\Runner;
use App\Services\Config\Env;
use App\Services\Config\Github;
use App\Commands\DockerCompose;
use Illuminate\Support\Facades\Artisan;
use App\Services\Docker\Local\Config;
use App\Services\Certificate\Handler;
use App\Commands\GlobalDocker\Start as GlobalStart;
use Illuminate\Filesystem\Filesystem;

/**
 * Local docker start command
 *
 * @package App\Commands\LocalDocker
 */
class Start extends BaseLocalDocker {

    public const ENV        = '/.env';
    public const ENV_SAMPLE = '/.env.sample';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'start {--b|browser      : Automatically open the project in your browser}
                                  {--p|path=        : Path to a specific local project folder}
                                  {--remove-orphans : Remove containers for services not in the compose file}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Starts your local SquareOne project, run anywhere in a project folder';

    /**
     * Execute the console command.
     *
     * @param  \App\Contracts\Runner              $runner
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Services\Certificate\Handler  $certificateHandler
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Services\Config\Github        $github
     * @param  \App\Services\Config\Env           $env
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle( Runner $runner, Config $config, Handler $certificateHandler, Filesystem $filesystem, Github $github, Env $env ): void {
        // Set a custom project path, if provided
        if ( $path = $this->option( 'path' ) ) {
            $config = $config->setPath( $path );
        }

        $this->info( sprintf( '➜ Starting %s...', $config->getProjectName() ) );

        // Check for composer .env file
        if ( $filesystem->exists( $config->getProjectRoot() . self::ENV_SAMPLE )
             && $filesystem->missing( $config->getProjectRoot() . self::ENV )
        ) {
            $this->createDefaultEnvFile( $env, $filesystem );
            $this->addEnvFile( $config, $env, $filesystem );
        }

        $this->prepareComposer( $config, $filesystem, $github );

        // Start global containers
        Artisan::call( GlobalStart::class, [], $this->getOutput() );

        $this->checkCertificates( $config, $certificateHandler );

        $this->syncVmTime( $runner );

        $workdir = getcwd();

        chdir( $config->getDockerDir() );

        $args = [
            '--project-name',
            $config->getProjectName(),
            'up',
            '-d',
            '--force-recreate',
        ];

        if ( $this->option('remove-orphans') ) {
            $args[] = '--remove-orphans';
        }

        // Start this project
        Artisan::call( DockerCompose::class, $args );

        chdir( $workdir );

        // Install hirak/prestissimo to speed up composer installs
        $this->prestissimo( $config, $filesystem );

        // Run composer
        Artisan::call( Composer::class, [
            'args' => [ 'install' ],
        ], $this->output );

        $url = $config->getProjectUrl();

        if ( $this->option( 'browser' ) ) {
            Artisan::call( Open::class, [
                'url' => $url,
            ] );
        }

        $this->info( sprintf( 'Project started: %s', $url ) );
    }

    /**
     * Ensure we have a valid GitHub auth token for composer
     *
     * @codeCoverageIgnore
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Services\Config\Github        $github
     */
    protected function prepareComposer( Config $config, Filesystem $filesystem, Github $github ): void {
        $composerDirectory = $config->getComposerVolume();

        if ( ! is_dir( $composerDirectory ) ) {
            mkdir( $composerDirectory );
        }

        $auth = $composerDirectory . '/auth.json';

        if ( $filesystem->missing( $auth ) ) {

            // Copy global auth.json to this project
            if ( $github->exists() ) {
                $github->copy( $composerDirectory );

                return;
            }

            $token =
                $this->secret( 'We have detected you have not configured a GitHub oAuth token. Please go to https//github.com/settings/tokens/new?scopes=repo and create one or enter an existing token' );

            // Save the default token to the so config directory.
            $github->save( $token );

            // Copy to local project.
            $github->copy( $composerDirectory );
        }
    }

    /**
     * Install hirak/prestissimo if not already installed.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     */
    protected function prestissimo( Config $config, Filesystem $filesystem ) {
        $composerDirectory = $config->getComposerVolume();

        $global = $composerDirectory . '/composer.lock';

        if ( $filesystem->missing( $global ) ) {

            chdir( $config->getDockerDir() );

            Artisan::call( DockerCompose::class, [
                '--project-name',
                $config->getProjectName(),
                'exec',
                'php-fpm',
                'composer',
                'global',
                'require',
                'hirak/prestissimo',
            ] );
        }
    }

    /**
     * Check if the proper CA / local certificates have been created.
     *
     * @codeCoverageIgnore
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Services\Certificate\Handler  $certificateHandler
     */
    protected function checkCertificates( Config $config, Handler $certificateHandler ): void {
        if ( ! $certificateHandler->caExists() ) {
            $this->warn( 'Missing CA certificate. Enter your sudo password when requested. Completely restart your browser after this is complete' );
            $certificateHandler->createCa();
        }

        $certificateHandler->createCertificate( $config->getProjectName() . '.tribe' );
    }

    /**
     * Ask the user for a license key
     *
     * @param  string  $variable  The environment variable name
     *
     * @return mixed
     */
    protected function licenseKey( string $variable ) {
        return $this->secret( sprintf( 'Enter your license key for %s (input is hidden)', $variable ) );
    }

    /**
     * Check if the default .env file exists, otherwise get the user to create it.
     *
     * @param  \App\Services\Config\Env           $env
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function createDefaultEnvFile( Env $env, Filesystem $filesystem ): void {
        if ( ! $env->exists() ) {
            $this->info( 'We’ll need to set up an .env file to install premium plugins. The default secrets are available here: https://moderntribe.1password.com/vaults/all/allitems/ydscklaxsrcy3l6rwoqoqz4xwa' );
            $file = storage_path( 'defaults/env' );
            $vars = Parser::parse( $filesystem->get( $file ) );

            $content = '';

            foreach ( $vars as $key => $value ) {
                $secret  = $this->licenseKey( $key );
                $content .= "${key}='${secret}'" . PHP_EOL;
            }

            // Save to config directory
            $env->save( $content );

            $this->info( sprintf( 'Data saved to %s', $file ) );
        }
    }

    /**
     * Add secrets to the project's .env file.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Services\Config\Env           $env
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function addEnvFile( Config $config, Env $env, Filesystem $filesystem ): void {
        $sample = $config->getProjectRoot() . self::ENV_SAMPLE;
        $file   = $config->getProjectRoot() . self::ENV;

        $vars = $env->diff( $sample );

        // The default .env file matches the one for this project exactly.
        if ( empty( $vars ) ) {
            $env->copy( $config->getProjectRoot() );
            $this->info( sprintf( 'Automatically created %s from default .env file', $file ) );

            return;
        }

        // Only notify the user of missing items if they do not contain a value
        if ( ! array_filter( $vars ) ) {
            $this->info( 'The default .env file is missing a few items for this project, please fill them in below' );
        }

        $missing = [];

        foreach ( $vars as $key => $value ) {
            // Only ask the user to set values for empty env vars
            if ( ! empty( $value ) ) {
                $missing[ $key ] = $value;
                continue;
            }

            $secret          = $this->licenseKey( $key );
            $missing[ $key ] = $secret;
        }

        $vars = array_merge( $env->getVars(), $missing );

        $content = '';

        foreach ( $vars as $key => $value ) {
            $content .= "${key}='${value}'" . PHP_EOL;
        }

        $filesystem->put( $file, $content );

        $this->info( sprintf( '.env file created at %s', $file ) );
    }

    /**
     * Synchronize VM time with system time.
     *
     * @codeCoverageIgnore
     *
     * This fixes a time sync bug on Mac OS.
     *
     * @param  \App\Contracts\Runner  $runner
     */
    protected function syncVmTime( Runner $runner ): void {
        $runner->run( 'docker run --privileged --rm phpdockerio/php7-fpm date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )->throw();
    }

}
