<?php

namespace Tests\Unit\Services\Docker\Dns;

use Tests\TestCase;
use RuntimeException;
use App\Commands\GlobalDocker\Start;
use App\Services\Docker\Dns\Handler;
use App\Services\Docker\Dns\Resolvers\Dhcp;
use App\Services\Docker\Dns\OsSupport\BaseSupport;
use App\Services\Docker\Dns\OsSupport\NullOs;

class HandlerTest extends TestCase {

    private $command;
    private $dhcp;
    private $os;

    protected function setUp(): void {
        parent::setUp();

        $this->command = $this->mock( Start::class );
        $this->dhcp    = $this->mock( Dhcp::class );
        $this->os      = $this->mock( BaseSupport::class );
    }

    public function test_it_checks_for_enabled_resolvers() {
        $this->dhcp->shouldReceive( 'supported' )->once()->andReturn( true );
        $this->dhcp->shouldReceive( 'enabled' )->once()->andReturn( true );

        $this->os->shouldReceive( 'resolvers' )->once()->andReturn( collect()->push( $this->dhcp ) );

        $handler = new Handler( $this->os );

        $enabled = $handler->enabled();

        $this->assertTrue( $enabled );
    }

    public function testItEnablesResolvers() {
        $this->dhcp->shouldReceive( 'supported' )->once()->andReturn( true );
        $this->dhcp->shouldReceive( 'enabled' )->once()->andReturn( false );
        $this->dhcp->shouldReceive( 'enable' )->once()->andReturn( true );

        $this->os->shouldReceive( 'supported' )->once()->andReturn( true );
        $this->os->shouldReceive( 'resolvers' )->once()->andReturn( collect()->push( $this->dhcp ) );

        $handler = new Handler( $this->os );

        $handler->enable( $this->command );
    }

    public function testItThrowsAnExceptionOnInvalidOs() {
        $this->expectException( RuntimeException::class );

        $this->os = $this->mock( NullOs::class );

        $handler = new Handler( $this->os );
        $this->os->shouldReceive( 'supported' )->once()->andReturn( false );
        $this->os->shouldReceive( 'resolvers' )->once()->andReturn( collect() );

        $enabled = $handler->enabled();

        // No supported or enabled resolvers, we'll pass this test and throw an exception after for the user.
        $this->assertTrue( $enabled );

        $handler->enable( $this->command );
    }

}
