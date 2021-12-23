<?php declare( strict_types=1 );

namespace Tests\Unit\Services\CustomCommands\Runners;

use App\Contracts\CustomCommandRunner;
use App\Services\CustomCommands\Runners\RunnerCollection;
use App\Services\CustomCommands\Runners\ServiceCommandRunner;
use Tests\TestCase;

final class RunnerCollectionTest extends TestCase {

    public function test_it_returns_correct_type() {
        $runner = $this->app->make( ServiceCommandRunner::class );

        $collection = new RunnerCollection( [ $runner ] );

        $this->assertInstanceOf( CustomCommandRunner::class, $collection->get( 0 ) );

    }
}
