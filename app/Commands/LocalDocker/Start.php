<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\Docker;
use App\Commands\DockerCompose;
use App\Commands\GlobalDocker\Start as GlobalStart;
use App\Commands\Open;
use App\Services\Certificate\Handler;
use App\Services\ComposerVersion;
use App\Services\Config\Env;
use App\Services\Config\Github;
use App\Services\Config\PhpStormMeta;
use App\Services\Docker\Container;
use App\Services\Docker\Local\Config;
use App\Services\Docker\SystemClock;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use M1\Env\Parser;
use Throwable;

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
                                  {--remove-orphans : Remove containers for services not in the compose file}
                                  {--skip-global    : Skip starting global containers}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Starts your local SquareOne project, run anywhere in a project folder';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \App\Services\Certificate\Handler  $certificateHandler
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Services\Config\Github        $github
     * @param  \App\Services\Config\Env           $env
     * @param  \App\Services\Config\PhpStormMeta  $phpStormMeta
     * @param  \App\Services\Docker\SystemClock   $clock
     * @param  \App\Services\ComposerVersion      $composerVersion
     * @param  \App\Services\Docker\Container     $container
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return void
     */
    public function handle(
        Config $config,
        Handler $certificateHandler,
        Filesystem $filesystem,
        Github $github,
        Env $env,
        PhpStormMeta $phpStormMeta,
        SystemClock $clock,
        ComposerVersion $composerVersion,
        Container $container
    ): void {
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
        $this->copyPhpStormMetaFile( $config, $phpStormMeta );

        // Start global containers
        if ( ! $this->option( 'skip-global') ) {
            Artisan::call( GlobalStart::class, [], $this->getOutput() );
        }

        $this->checkCertificates( $config, $certificateHandler );

        $clock->sync();

        $workdir = getcwd();

        chdir( $config->getDockerDir() );

        $args = [
            '--project-name',
            $config->getProjectName(),
            'up',
            '-d',
            '--force-recreate',
        ];

        if ( $this->option( 'remove-orphans' ) ) {
            $args[] = '--remove-orphans';
        }

        // Start this project
        Artisan::call( DockerCompose::class, $args );

        chdir( $workdir );

        // Install hirak/prestissimo to speed up composer installs
        $this->prestissimo( $config, $filesystem, $composerVersion, $container );

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
                $this->secret( 'We have detected you have not configured a GitHub oAuth token. Please go to https://github.com/settings/tokens/new?scopes=repo and create one or enter an existing token' );

            // Save the default token to the so config directory.
            $github->save( (string) $token );

            // Copy to local project.
            $github->copy( $composerDirectory );
        }
    }

    /**
     * Install hirak/prestissimo if not already installed.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Services\ComposerVersion      $composerVersion
     * @param  \App\Services\Docker\Container     $container
     */
    protected function prestissimo( Config $config, Filesystem $filesystem, ComposerVersion $composerVersion, Container $container ): void {
        if ( ! $composerVersion->isVersionOne() ) {
            return;
        }

        $composerDirectory = $config->getComposerVolume();

        $global = $composerDirectory . '/composer.lock';

        if ( $filesystem->missing( $global ) ) {

            Artisan::call( Docker::class, [
                'exec',
                '--tty',
                $container->getId(),
                'composer',
                'global',
                'require',
                'hirak/prestissimo',
            ] );
        }
    }

    /**
     * Check if the proper CA / local and test domain certificates have been created.
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

        $projectName = $config->getProjectName();

        $certificateHandler->createCertificate( $projectName . '.tribe' );
        $certificateHandler->createCertificate( $projectName . 'test.tribe' );
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
            $this->info( 'We’ll need to set up an .env file to install premium plugins. The default secrets are available here: https://m.tri.be/soenv10' );
            $file = storage_path( 'defaults/env' );
            $vars = Parser::parse( $filesystem->get( $file ) );

            $content = '';

            foreach ( $vars as $key => $value ) {
                $secret  = $this->licenseKey( $key );
                $content .= "$key='$secret'" . PHP_EOL;
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
            $content .= "$key='$value'" . PHP_EOL;
        }

        $filesystem->put( $file, $content );

        $this->info( sprintf( '.env file created at %s', $file ) );
    }

    protected function copyPhpStormMetaFile( Config $config, PhpStormMeta $phpStormMeta ): void {
        $projectRoot = $config->getProjectRoot();
        $warning     = 'Unable to copy .phpstorm.meta.php file';

        if ( ! $phpStormMeta->existsInProject( $projectRoot ) ) {
            try {
                if ( ! $phpStormMeta->copy( $projectRoot ) ) {
                    $this->warn( $warning );
                }
            } catch ( Throwable $e ) {
                $this->warn( $warning );
            }
        }
    }

}
