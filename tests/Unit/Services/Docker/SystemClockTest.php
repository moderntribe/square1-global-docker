<?php

namespace Tests\Unit\Services\Docker;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Services\Docker\SystemClock;

class SystemClockTest extends TestCase {

    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
    }

    public function test_it_syncs_the_docker_container_with_the_system_clock() {
        $this->runner->shouldReceive( 'throw' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )
                     ->with( 'docker run --privileged --rm php:7.4.7-fpm date -s "$(date -u "+%Y-%m-%d %H:%M:%S")"' )
                     ->once()
                     ->andReturnSelf();

        $clock = new SystemClock( $this->runner );

        $clock->sync();
    }

}
