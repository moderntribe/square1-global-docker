<?php

namespace Tests\Feature\Commands;

use App\Commands\GlobalDocker\Portainer;
use App\Runners\CommandRunner;

class PortainerTest extends BaseCommandTest {

    private $runner;

    public function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
    }

    public function testItOpensPortainerUrl() {
        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'which xdg-open' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'which open' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'successful' )
                     ->once()
                     ->andReturnTrue();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'xdg-open ' . Portainer::PORTAINER_URL )->andReturnSelf();

        $command = $this->app->make( Portainer::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Launching Portainer at ' . Portainer::PORTAINER_URL, $tester->getDisplay() );
    }

}
