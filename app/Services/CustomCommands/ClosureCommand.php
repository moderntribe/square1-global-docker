<?php declare(strict_types=1);

namespace App\Services\CustomCommands;

use Illuminate\Foundation\Console\ClosureCommand as FoundationClosureCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClosureCommand extends FoundationClosureCommand {

    /**
     * Overload the existing execute method and pass all inputs to the
     * callback.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface    $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return int
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int {
        $inputs = array_merge( $input->getArguments(), $input->getOptions() );

        return (int) $this->laravel->call(
            $this->callback->bindTo( $this, $this ), $inputs
        );
    }
}
