<?php declare( strict_types=1 );

namespace App\Runners;

use App\Contracts\Runner;
use TitasGailius\Terminal\Builder;

/**
 * Extend Terminal's command builder
 *
 * @package App\Runners
 */
class CommandRunner extends Builder implements Runner {

    /**
     * Timeout.
     *
     * @var \DateTimeInterface|\DateInterval|int|null $ttl
     */
    protected $timeout = null;

    /**
     * Max time since last output.
     *
     * @var mixed
     */
    protected $idleTimeout = null;

}
