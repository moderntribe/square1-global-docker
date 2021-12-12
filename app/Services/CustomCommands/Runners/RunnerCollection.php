<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runners;

use App\Contracts\CustomCommandRunner;
use Illuminate\Support\Collection;

/**
 * A collection of custom command runner pipes.
 */
class RunnerCollection extends Collection {

    public function get( $key, $default = null ): CustomCommandRunner {
        return parent::get( $key, $default );
    }

}
