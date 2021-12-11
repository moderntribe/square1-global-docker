<?php declare(strict_types=1);

namespace App\Services\CustomCommands;

use Spatie\DataTransferObject\DataTransferObjectCollection;

/**
 * A collection of custom command definitions.
 */
class CommandCollection extends DataTransferObjectCollection {

    public function current(): CommandDefinition {
        return parent::current();
    }

}
