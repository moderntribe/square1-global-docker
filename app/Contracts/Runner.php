<?php declare( strict_types=1 );

namespace App\Contracts;

use IteratorAggregate;
use Symfony\Component\Process\Process;

/**
 * Runner Interface to run operating system commands.
 *
 * @mixin \TitasGailius\Terminal\Builder
 * @mixin \App\Runners\CommandRunner
 *
 * @package App\Contracts
 */
interface Runner {

    /**
     * Execute a given command.
     *
     * @param  string|array $command The command to run.
     * @param  callable|null $output
     *
     * @return mixed
     */
    public function run($command = null, callable $output = null);

    /**
     * Run a given process.
     *
     * @param  \Symfony\Component\Process\Process  $process
     *
     * @return IteratorAggregate
     */
    public function runProcess(Process $process);
}
