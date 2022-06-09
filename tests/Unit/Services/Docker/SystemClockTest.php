<?php declare(strict_types=1);

namespace Tests\Unit\Services\Docker;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Services\Docker\SystemClock;

final class SystemClockTest extends TestCase {

    /**
     * @var \App\Contracts\Runner
     */
    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
    }

    public function test_it_syncs_the_docker_container_with_the_system_clock() {
        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --rm --privileged alpine hwclock -s' )
                     ->once()
                     ->andReturnSelf();

        $clock = new SystemClock( $this->runner );

        $clock->sync();
    }

}
