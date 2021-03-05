<?php declare( strict_types=1 );

namespace App\Services\Nfs;

use Throwable;
use App\Contracts\Runner;
use Illuminate\Filesystem\Filesystem;
use App\Exceptions\SystemExitException;

/**
 * Validate NFS is properly configured.
 *
 * @package App\Services\Nfs
 */
class NfsValidator {

    public const NFS_CONF = '/etc/nfs.conf';

    /**
     * The Illuminate Filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * Controls the nfsd binary.
     *
     * @var \App\Services\Nfs\Nfsd
     */
    protected $nfsd;

    /**
     * Whether we should restart nfsd.
     *
     * @var bool
     */
    protected $restart = false;

    /**
     * Validator constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \App\Contracts\Runner              $runner
     * @param  \App\Services\Nfs\Nfsd             $nfsd
     */
    public function __construct( Filesystem $filesystem, Runner $runner, Nfsd $nfsd ) {
        $this->filesystem = $filesystem;
        $this->runner     = $runner;
        $this->nfsd       = $nfsd;
    }

    /**
     * Validate NFS Export Tables are configured correctly.
     *
     * @param  string  $exportsFilePath
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    public function validate( string $exportsFilePath ): void {
        $this->verifyExportsFileExists( $exportsFilePath )
             ->verifyNfsReservedPort();
    }

    /**
     * nfsd will not start without a config file. Creating the file triggers a nfsd start
     * so give it time to start up.
     *
     * @param  string  $exportsFilePath
     *
     * @return \App\Services\Nfs\NfsValidator
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    protected function verifyExportsFileExists( string $exportsFilePath ): self {
        if ( $this->filesystem->exists( $exportsFilePath ) ) {
            return $this;
        }

        $this->runner->with( [ 'path' => $exportsFilePath, ] )
                     ->run( 'sudo touch {{ $path }}' )
                     ->throw();

        sleep( 5 );

        return $this;
    }

    /**
     * Ensure the NFS server has nfs.server.mount.require_resv_port = 0
     * otherwise NFS mounts usually fail.
     *
     * @return $this
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Symfony\Component\Process\Exception\ProcessFailedException
     */
    protected function verifyNfsReservedPort(): self {
        $line    = 'nfs.server.mount.require_resv_port = 0';
        $content = $this->filesystem->get( self::NFS_CONF );

        if ( ! str_contains( $content, $line ) ) {
            $this->runner->with( [ 'content' => $line, 'path' => self::NFS_CONF ] )
                         ->run( 'echo {{ $content }} | sudo tee -a {{ $path }}' )
                         ->throw();
            $this->restart = true;
        }

        return $this;
    }

    /**
     * Test the exports table on a temporary file. The thrown exception should contain
     * the errors required to fix to continue.
     *
     * @param  string  $tempFile  The path to the temporary /etc/exports file to validate.
     *
     * @throws \App\Exceptions\SystemExitException
     */
    public function checkExports( string $tempFile ): void {
        try {
            $this->nfsd->check( $tempFile );
        } catch ( Throwable $exception ) {
            $this->filesystem->delete( $tempFile );
            throw new SystemExitException( 'There was an error in the /etc/exports file when trying to set up the NFS share: ' . $exception->getMessage() );
        }
    }

    /**
     * See if we should restart the nfsd server.
     */
    public function __destruct() {
        if ( ! $this->restart ) {
            return;
        }

        $this->nfsd->restart();

        sleep( 5 );
    }

}
