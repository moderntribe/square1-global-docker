<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\DockerCompose;
use App\Contracts\ArgumentRewriter;
use App\Services\Docker\Local\Config;
use App\Traits\ArgumentRewriterTrait;
use Illuminate\Support\Facades\Artisan;

/**
 * Local docker start command.
 *
 * @package App\Commands\LocalDocker
 */
class Composer extends BaseLocalDocker implements ArgumentRewriter {

    use ArgumentRewriterTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'composer {args* : arguments passed to the composer binary}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run a composer command in the local docker container';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Docker\Local\Config  $config
     *
     * @return void
     */
    public function handle( Config $config ): void {
        $params = [
            '--project-name',
            $config->getProjectName(),
            'exec',
            'php-fpm',
            $this->arguments()['command'],
        ];

        $args = $this->rewriteVersionArguments( $this->argument( 'args' ) );

        $params = array_merge( $params, $args, [
            '-d',
            '/application/www',
        ] );

        chdir( $config->getDockerDir() );

        Artisan::call( DockerCompose::class, $params );

        $this->info( 'Done.' );
    }

}
