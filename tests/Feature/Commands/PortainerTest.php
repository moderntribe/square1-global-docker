<?php

namespace Tests\Feature\Commands;

use App\Commands\BaseCommand;
use App\Runners\CommandRunner;
use App\Commands\GlobalDocker\Portainer;

class PortainerTest extends BaseCommandTest {

    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
    }

    public function test_it_opens_portainer_url() {
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

        $this->assertSame( BaseCommand::EXIT_SUCCESS, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Launching Portainer at ' . Portainer::PORTAINER_URL, $tester->getDisplay() );
    }

}
