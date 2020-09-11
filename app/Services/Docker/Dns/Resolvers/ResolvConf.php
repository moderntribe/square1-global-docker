<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns\Resolvers;

use App\Contracts\Runner;
use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ResolvConf extends BaseResolver {

    /**
     * The path to the custom resolv file dependant on the operating system in use.
     *
     * e.g. resolv.conf.head, /etc/resolvconf/resolv.conf.d/head
     *
     * @var string
     */
    protected $file;

    /**
     * ResolvConf constructor.
     *
     * @param  \App\Contracts\Runner              $runner
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  string                             $file
     */
    public function __construct( Runner $runner, Filesystem $filesystem, string $file ) {
        parent::__construct( $runner, $filesystem );

        $this->file = $file;
    }

    /**
     * If this os has the proper file, it's supported.
     *
     * @return bool
     */
    public function supported(): bool {
        if ( $this->filesystem->exists( '/etc/resolv.conf' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check if the relevant os resolv file contains the correct nameservers.
     *
     * @return bool
     */
    public function enabled(): bool {
        try {
            $content = $this->filesystem->get( $this->file );

            if ( str_contains( $content, 'nameserver 127.0.0.1' ) ) {
                return true;
            }
        } catch ( FileNotFoundException $exception ) {
            return false;
        }

        return false;
    }

    /**
     * Enable nameservers for resolv.conf for the current OS.
     *
     * @param  \LaravelZero\Framework\Commands\Command  $command
     */
    public function enable( Command $command ): void {
        $command->task( sprintf( '<comment>➜ Adding 127.0.0.1 nameservers to %s</comment>', $this->file ), call_user_func( [ $this, 'addNameservers' ] ) );
    }

    /**
     * Add nameservers to the appropriate resolv.conf.head / head / tribe file.
     *
     */
    public function addNameservers(): void {
        $this->runner->run( 'echo "nameserver 127.0.0.1" | sudo tee ' . $this->file )->throw();
    }

}
