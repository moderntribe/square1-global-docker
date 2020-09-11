<?php declare( strict_types=1 );

namespace App\Contracts;

use LaravelZero\Framework\Commands\Command;

/**
 * Resolve different DNS strategies on different operating systems.
 *
 * @package App\Contracts
 */
interface Resolvable {

    /**
     * Whether this resolver is supported/installed.
     *
     * @return bool
     */
    public function supported(): bool;

    /**
     * Whether this resolver is already enabled.
     *
     * @return bool
     */
    public function enabled(): bool;

    /**
     * The tasks to enable this resolver.
     *
     * @param  \LaravelZero\Framework\Commands\Command  $command
     */
    public function enable( Command $command ): void;

}
