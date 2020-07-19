<?php declare( strict_types=1 );

namespace App\Services\Docker\Dns\Resolvers;

use App\Contracts\Resolvable;
use App\Contracts\Runner;
use Illuminate\Filesystem\Filesystem;

/**
 * Base DNS Resolver.
 *
 * @package App\Services\Docker\Dns\Resolvers
 */
abstract class BaseResolver implements Resolvable {

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * Illuminate Filesystem.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * BaseResolver constructor.
     *
     * @param  \App\Contracts\Runner  $runner
     */
    public function __construct( Runner $runner, Filesystem $filesystem ) {
        $this->runner     = $runner;
        $this->filesystem = $filesystem;
    }

}
