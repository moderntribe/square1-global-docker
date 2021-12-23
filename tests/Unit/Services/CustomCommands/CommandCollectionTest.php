<?php declare( strict_types=1 );

namespace Tests\Unit\Services\CustomCommands;

use App\Services\CustomCommands\CommandCollection;
use App\Services\CustomCommands\CommandDefinition;
use Tests\TestCase;

final class CommandCollectionTest extends TestCase {

    public function test_it_returns_correct_type() {
        $command = new CommandDefinition( [
                'signature'   => 'test',
                'description' => 'A test command definition',
        ] );

        $collection = new CommandCollection( [ $command ] );

        $this->assertInstanceOf( CommandDefinition::class, $collection->current() );

    }
}
