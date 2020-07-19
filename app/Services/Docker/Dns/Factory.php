<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns;

use App\Contracts\Runner;
use App\Services\Docker\Dns\OsSupport\Linux;
use App\Services\Docker\Dns\OsSupport\MacOs;
use App\Services\Docker\Dns\OsSupport\NullOs;
use App\Services\Docker\Dns\Resolvers\Dhcp;
use App\Services\Docker\Dns\Resolvers\ResolvConf;
use App\Services\Docker\Dns\Resolvers\SystemdResolved;
use App\Services\OperatingSystem;
use App\Services\Docker\Dns\OsSupport\BaseSupport;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

/**
 * Handles adding the docker name server across multiple systems
 *
 * @package App\Services
 */
class Factory {

    /**
     * The operating system object.
     *
     * @var \App\Services\OperatingSystem
     */
    protected $os;

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * Filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;


    /**
     * Handler constructor.
     *
     * @param  \App\Services\OperatingSystem      $os
     * @param  \App\Contracts\Runner              $runner
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     */
    public function __construct( OperatingSystem $os, Runner $runner, Filesystem $filesystem ) {
        $this->os         = $os;
        $this->runner     = $runner;
        $this->filesystem = $filesystem;
    }

    /**
     * The Resolver Factory.
     *
     * @param  \Illuminate\Support\Collection  $resolvers
     *
     * @return \App\Services\Docker\Dns\OsSupport\BaseSupport
     */
    public function make( Collection $resolvers ): BaseSupport {
        if ( OperatingSystem::LINUX === $this->os->getFamily() ) {
            $resolvers->push(
                new Dhcp( $this->runner, $this->filesystem ),
                new SystemdResolved( $this->runner, $this->filesystem )
            );

            // Debian
            if ( OperatingSystem::DEBIAN === $this->os->getLinuxFlavor() ) {
                $resolvers->push( new ResolvConf( $this->runner, $this->filesystem, '/etc/resolvconf/resolv.conf.d/head' ) );
            }

            // Arch
            if ( OperatingSystem::ARCH === $this->os->getLinuxFlavor() ) {
                $resolvers->push( new ResolvConf( $this->runner, $this->filesystem, '/etc/resolv.conf.head' ) );
            }

            return new Linux( $resolvers );
        }

        if ( OperatingSystem::MAC_OS === $this->os->getFamily() ) {
            $resolvers->push( new ResolvConf( $this->runner, $this->filesystem, '/etc/resolvers/tribe' ) );

            return new MacOs( $resolvers );
        }

        return new NullOs( $resolvers );
    }

}
