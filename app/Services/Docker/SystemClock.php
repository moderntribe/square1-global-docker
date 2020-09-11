<?php declare( strict_types=1 );

namespace App\Services\Docker;

use App\Contracts\Runner;

/**
 * Class SystemClock
 *
 * @package App\Services\Docker
 */
class SystemClock {

    /**
     * The command runner.
     *
     * @var \App\Contracts\Runner
     */
    protected $runner;

    /**
     * SystemClock constructor.
     *
     * @param  \App\Contracts\Runner  $runner
     */
    public function __construct( Runner $runner ) {
        $this->runner = $runner;
    }

    /**
     * Synchronize VM time with system time.
     *
     * This fixes a docker host/container time sync bug on Mac OS.
     */
    public function sync(): void {
        $this->runner->run( 'docker run --privileged --rm php:7.4.7-fpm date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )->throw();
    }

}
