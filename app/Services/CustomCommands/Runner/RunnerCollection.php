<?php declare(strict_types=1);

namespace App\Services\CustomCommands\Runner;

use App\Contracts\CustomCommandRunner;
use Illuminate\Support\Collection;

/**
 * A collection of custom command runners.
 */
class RunnerCollection extends Collection {

    // Runner collection keys
    public const SERVICE       = 'service';
    public const SERVICE_MULTI = 'serviceMulti';
    public const HOST          = 'host';

    public function get( $key, $default = null ): CustomCommandRunner {
        return parent::get( $key, $default );
    }

}
