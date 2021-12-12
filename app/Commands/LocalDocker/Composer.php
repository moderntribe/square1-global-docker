<?php declare( strict_types=1 );

namespace App\Commands\LocalDocker;

use App\Commands\Docker;
use App\Contracts\ArgumentRewriter;
use App\Services\Docker\Container;
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
     * @param  \App\Services\Docker\Container  $container
     *
     * @return int
     */
    public function handle( Container $container ): int {
        $containerId = $container->getId();

        if ( empty( $containerId ) ) {
            $this->error( 'Unable to find container. Has this project been started?' );
            return self::EXIT_ERROR;
        }

        $params = [
            'exec',
            '--tty',
            $containerId,
            $this->arguments()['command'],
        ];

        $args = $this->rewriteVersionArguments( $this->argument( 'args' ) );

        $params = array_merge( $params, $args, [
            '-d',
            '/application/www',
        ] );

        Artisan::call( Docker::class, $params );

        return self::EXIT_SUCCESS;
    }

}
