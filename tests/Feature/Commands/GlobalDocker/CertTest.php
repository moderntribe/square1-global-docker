<?php

namespace Tests\Feature\Commands\GlobalDocker;

use InvalidArgumentException;
use App\Commands\GlobalDocker\Cert;
use App\Services\Docker\SystemClock;
use App\Services\Certificate\Handler;
use Tests\Feature\Commands\BaseCommandTester;

class CertTest extends BaseCommandTester {

    protected $handler;
    protected $clock;

    protected function setUp(): void {
        parent::setUp();

        $this->handler = $this->mock( Handler::class );
        $this->clock   = $this->mock( SystemClock::class );
    }

    public function test_it_creates_certificate() {
        $domain = 'squareone.tribe';

        $this->handler->shouldReceive( 'createCertificate' )->with( $domain )->once();
        $this->clock->shouldReceive( 'sync' )->once();

        $command = $this->app->make( Cert::class );
        $tester  = $this->runCommand( $command, [
            'domain' => $domain,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Generating a certificate for ' . $domain, $tester->getDisplay() );
    }

    public function test_it_throws_exeception_on_invalid_domain() {
        $this->expectException( InvalidArgumentException::class );

        $invalidDomain = 'squareone';

        $command = $this->app->make( Cert::class );
        $tester  = $this->runCommand( $command, [
            'domain' => $invalidDomain,
        ] );

        $this->assertSame( 1, $tester->getStatusCode() );
    }

}
