<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns;

use App\Contracts\Runner;
use App\Services\OperatingSystem;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use App\Services\Docker\Dns\Resolvers\Dhcp;
use App\Services\Docker\Dns\OsSupport\Linux;
use App\Services\Docker\Dns\OsSupport\MacOs;
use App\Services\Docker\Dns\OsSupport\NullOs;
use App\Services\Docker\Dns\Resolvers\Scutil;
use App\Services\Docker\Dns\Resolvers\ResolvConf;
use App\Services\Docker\Dns\Resolvers\Openresolv;
use App\Services\Docker\Dns\OsSupport\BaseSupport;
use App\Services\Docker\Dns\Resolvers\SystemdResolved;

/**
 * Handles adding the docker name server across multiple systems
 *
 * @package App\Services
 */
class Factory {

    /**
     * The operating system instance.
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
     * @param   \App\Services\OperatingSystem      $os
     * @param   \App\Contracts\Runner              $runner
     * @param   \Illuminate\Filesystem\Filesystem  $filesystem
     */
    public function __construct( OperatingSystem $os, Runner $runner, Filesystem $filesystem ) {
        $this->os         = $os;
        $this->runner     = $runner;
        $this->filesystem = $filesystem;
    }

    /**
     * The Resolver Factory.
     *
     * @param   \Illuminate\Support\Collection  $resolvers
     *
     * @return \App\Services\Docker\Dns\OsSupport\BaseSupport
     */
    public function make( Collection $resolvers ): BaseSupport {
        if ( OperatingSystem::LINUX === $this->os->getFamily() ) {
            $resolvers->push(
                new Openresolv( $this->runner, $this->filesystem, '/etc/resolv.conf.head' ),
                new ResolvConf( $this->runner, $this->filesystem, '/etc/resolvconf/resolv.conf.d/head' ),
                new Dhcp( $this->runner, $this->filesystem ),
                new SystemdResolved( $this->runner, $this->filesystem )
            );

            return new Linux( $resolvers );
        }

        if ( OperatingSystem::MAC_OS === $this->os->getFamily() ) {
            $resolvers->push( new Scutil( $this->runner, $this->filesystem, '/etc/resolver/tribe' ) );

            return new MacOs( $resolvers );
        }

        return new NullOs( $resolvers );
    }

}
