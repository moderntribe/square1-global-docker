<?php

namespace Tests\Feature\Commands\GlobalDocker;

use App\Commands\GlobalDocker\MyAdmin;
use App\Commands\Open;
use App\Runners\CommandRunner;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\Commands\BaseCommandTester;

class MyAdminTest extends BaseCommandTester {

    private $runner;

    protected function setUp(): void {
        parent::setUp();

        $this->runner = $this->mock( CommandRunner::class );
    }

    public function test_it_runs_php_myadmin_docker_container() {
        $this->runner->shouldReceive( 'run' )
               ->with( 'docker start tribe-phpmyadmin' )
               ->once()
               ->andReturn( $this->runner );

        $this->runner->shouldReceive( 'ok' )
               ->twice()
               ->andReturn( false );

        $this->runner->shouldReceive( 'throw' )
               ->once()
               ->andReturn( $this->runner );

        $this->runner->shouldReceive( 'run' )
               ->once()
               ->with( 'docker rm /tribe-phpmyadmin' )
               ->andReturn( $this->runner );

        $this->runner->shouldReceive( 'tty' )
                     ->once()
                     ->with( true )
                     ->andReturn( $this->runner );

        $this->runner->shouldReceive( 'run' )
               ->twice()
               ->with( 'docker run -d --name tribe-phpmyadmin --link tribe-mysql:db --network="global_proxy" -p 8080:80 phpmyadmin/phpmyadmin' )
               ->andReturn( $this->runner );

        $open = $this->mock( Open::class );

        Artisan::swap( $open );

        $open->shouldReceive( 'call' )
             ->with( Open::class, ['url' => 'http://localhost:8080/'] )
             ->once();

        $command = $this->app->make( MyAdmin::class );
        $tester  = $this->runCommand( $command, [] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Starting phpMyAdmin...', $tester->getDisplay() );

    }

}
