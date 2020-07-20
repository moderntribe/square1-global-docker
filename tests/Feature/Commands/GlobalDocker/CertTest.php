<?php

namespace Tests\Feature\Commands\GlobalDocker;

use InvalidArgumentException;
use App\Commands\GlobalDocker\Cert;
use App\Commands\GlobalDocker\Restart;
use App\Services\Certificate\Handler;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Commands\BaseCommandTest;

class CertTest extends BaseCommandTest {

    protected $handler;
    protected $restart;

    protected function setUp(): void {
        parent::setUp();

        $this->handler = $this->mock( Handler::class );
        $this->restart = $this->mock( Restart::class );
        Artisan::swap( $this->restart );
    }

    public function test_it_creates_certificate() {
        $domain = 'squareone.tribe';

        $this->handler->shouldReceive( 'createCertificate' )->with( $domain )->once();
        $this->restart->shouldReceive( 'call' )->with( Restart::class )->once();

        $command = $this->app->make( Cert::class );
        $tester  = $this->runCommand( $command, [
            'domain' => $domain,
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Generating a certificate for ' . $domain, $tester->getDisplay() );
        $this->assertStringContainsString( 'Restarting global docker', $tester->getDisplay() );
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
