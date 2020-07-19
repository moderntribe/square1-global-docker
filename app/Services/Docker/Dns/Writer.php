<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns;

use App\Contracts\Runner;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Writer
 *
 * @package App\Services\Docker\Dns
 */
class Writer {

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * Symfony Filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Writer constructor.
     *
     * @param   \App\Contracts\Runner  $runner
     * @param   Filesystem             $filesystem
     */
    public function __construct( Runner $runner, Filesystem $filesystem ) {
        $this->runner     = $runner;
        $this->filesystem = $filesystem;
    }

    /**
     * Write nameservers to a resolver file.
     *
     * @param  string  $file          The location of the configuration file
     * @param  string  $directory     The directory where the configuration file lives
     * @param  string  $nameserverIp  The nameserver IP address to save
     *
     * @return bool
     */
    public function write( string $file, string $directory, string $nameserverIp = '127.0.0.1' ): bool {
        $tmpFile = $this->filesystem->tempnam( '/tmp', 'sq1' );

        $this->filesystem->dumpFile( $tmpFile, sprintf( 'nameserver %s', $nameserverIp ) );

        $this->filesystem->chmod( $tmpFile, 0644, umask() );

        if ( ! $this->filesystem->exists( $directory ) ) {
            $this->runner->with( [
                'directory' => $directory,
            ] )->run( 'sudo mkdir -p {{ $directory }}' )
                         ->throw();
        }

        $this->runner->with( [
            'from' => $tmpFile,
            'to'   => $file,
        ] )->run( 'sudo cp {{ $from }} {{ $to }}' )
                     ->throw();

        unset( $tmpFile );

        return true;
    }
}
