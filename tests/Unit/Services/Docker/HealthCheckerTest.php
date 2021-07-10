<?php

namespace Tests\Unit\Services\Docker;

use App\Services\Docker\HealthChecker;
use Tests\TestCase;
use App\Runners\CommandRunner;

class HealthCheckerTest extends TestCase {

    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
    }

    public function test_it_finds_a_healthy_container() {
        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [ 'container' => 'name=tribe-mysql' ] )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'command' )
                     ->once()
                     ->with( 'docker ps --filter {{ $container }} --filter "health=healthy" --format "{{ .Status }}"' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )->once()->andReturn( 'up 4 seconds (healthy)' );

        $this->assertTrue( ( new HealthChecker( $this->runner ) )->healthy( 'tribe-mysql' ) );
    }

    public function test_it_does_not_find_a_healthy_container() {
        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [ 'container' => 'name=tribe-mysql' ] )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'command' )
                     ->once()
                     ->with( 'docker ps --filter {{ $container }} --filter "health=healthy" --format "{{ .Status }}"' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )->once()->andReturn( '' );

        $this->assertFalse( ( new HealthChecker( $this->runner ) )->healthy( 'tribe-mysql' ) );
    }

}
