<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runner;

use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;

/**
 * Select the proper runner strategy based on the command.
 */
class RunnerFactory {

    /**
     * A collection of custom command runners.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $runners;

    public function __construct( RunnerCollection $runners ) {
        $this->runners = $runners;
    }

    /**
     * Fetch the appropriate runner strategy.
     *
     * @param  \App\Services\CustomCommands\CommandDefinition  $command
     *
     * @return \App\Contracts\CustomCommandRunner
     */
    public function make( CommandDefinition $command ): CustomCommandRunner {
        if ( is_array( $command->cmd ) ) {
            return $this->runners->get( RunnerCollection::SERVICE_MULTI );
        }

        if ( empty( $command->service ) ) {
            return $this->runners->get( RunnerCollection::HOST );
        }

        return $this->runners->get( RunnerCollection::SERVICE );
    }

}
