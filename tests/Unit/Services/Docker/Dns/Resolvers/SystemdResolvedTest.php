<?php


namespace Tests\Unit\Services\Docker\Dns\Resolvers;

use App\Commands\GlobalDocker\Start;
use Mockery;
use Tests\TestCase;
use App\Runners\CommandRunner;
use Illuminate\Filesystem\Filesystem;
use App\Services\Docker\Dns\Resolvers\SystemdResolved;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class SystemdResolvedTest extends TestCase {

    private $runner;
    private $filesystem;
    private $command;

    public function setUp(): void {
        parent::setUp();

        $this->runner     = $this->mock( CommandRunner::class );
        $this->filesystem = $this->mock( Filesystem::class );
        $this->command    = $this->mock( Start::class );
    }

    public function test_it_is_supported_when_systemd_is_active() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'systemctl status systemd-resolved' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturnTrue();

        $this->runner->shouldReceive( '__toString' )
                     ->once()
                     ->andReturn( 'Sample SystemD Resolved Output... active (running)' );

        $resolver = new SystemdResolved( $this->runner, $this->filesystem );

        $this->assertTrue( $resolver->supported() );
    }

    public function test_it_is_not_supported_when_systemd_is_inactive() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'systemctl status systemd-resolved' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturn( true );

        $this->runner->shouldReceive( '__toString' )
                     ->once()
                     ->andReturn( 'Active: inactive (dead)' );

        $resolver = new SystemdResolved( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->supported() );
    }

    public function test_it_is_not_supported_when_systemd_resovled_is_disabled() {
        $this->runner->shouldReceive( 'run' )
                     ->with( 'systemctl status systemd-resolved' )
                     ->once()
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'ok' )
                     ->once()
                     ->andReturnFalse();

        $resolver = new SystemdResolved( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->supported() );
    }

    public function test_it_is_enabled() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( '/etc/systemd/resolved.conf' )
                         ->once()
                         ->andReturn( 'DNS=127.0.0.1 1.1.1.1' );

        $resolver = new SystemdResolved( $this->runner, $this->filesystem );

        $this->assertTrue( $resolver->enabled() );
    }

    public function test_it_is_disabled_with_missing_content() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( '/etc/systemd/resolved.conf' )
                         ->once()
                         ->andReturn( 'DNS=1.1.1.1 1.0.0.1' );

        $resolver = new SystemdResolved( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->enabled() );
    }

    public function test_it_is_disabled_with_missing_resolved_conf() {
        $this->filesystem->shouldReceive( 'get' )
                         ->with( '/etc/systemd/resolved.conf' )
                         ->once()
                         ->andThrow( FileNotFoundException::class );

        $resolver = new SystemdResolved( $this->runner, $this->filesystem );

        $this->assertFalse( $resolver->enabled() );
    }

    public function test_it_can_be_enabled() {
        $this->runner->shouldReceive( 'with' )->with( [
            'custom-resolved-conf' => storage_path( 'dns/debian/resolved.conf' ),
            'system-resolved-conf' => '/etc/systemd/resolved.conf',
        ] )->once()->andReturnSelf();

        $this->runner->shouldReceive( 'run' )->with( 'sudo cp {{ custom-resolved-conf }} {{ system-resolved-conf }}' )->once()->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )->times( 3 )->andReturnSelf();

        $this->runner->shouldReceive( 'run' )->with( 'sudo ln -fsn /run/systemd/resolve/resolv.conf /etc/resolv.conf' )->once()->andReturnSelf();
        $this->runner->shouldReceive( 'run' )->with( 'sudo systemctl restart systemd-resolved' )->once()->andReturnSelf();

        $this->command->shouldReceive( 'task' )
                      ->with( '<comment>➜ Copying custom /etc/systemd/resolved.conf</comment>', null )
                      ->once()
                      ->andReturnTrue();

        $this->command->shouldReceive( 'task' )
                      ->with( '<comment>➜ Symlinking /run/systemd/resolve/resolv.conf /etc/resolv.conf</comment>', null )
                      ->once()
                      ->andReturnTrue();

        $this->command->shouldReceive( 'task' )
                      ->with( '<comment>➜ Restarting systemd-resolved</comment>', null )
                      ->once()
                      ->andReturnTrue();


        $resolver = new SystemdResolved( $this->runner, $this->filesystem );

        $resolver->enable( $this->command );
    }

}
