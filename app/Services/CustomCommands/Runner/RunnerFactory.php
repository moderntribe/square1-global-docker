<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runner;

use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\CommandDefinition;
use Illuminate\Support\Collection;

/**
 * Select the proper runner strategy based on the command.
 */
class RunnerFactory {

    public const SERVICE       = 'service';
    public const SERVICE_MULTI = 'serviceMulti';
    public const HOST          = 'host';

    /**
     * A collection of custom command runners.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $runners;

    public function __construct( Collection $runners ) {
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
            return $this->runners->get( self::SERVICE_MULTI );
        }

        if ( empty( $command->service ) ) {
            return $this->runners->get( self::HOST );
        }

        return $this->runners->get( self::SERVICE );
    }

}
