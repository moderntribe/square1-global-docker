<?php

namespace Tests\Unit\Services\Docker\Dns;

use Tests\TestCase;
use App\Runners\CommandRunner;
use App\Services\OperatingSystem;
use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use App\Services\Docker\Dns\Factory;
use App\Services\Docker\Dns\OsSupport\Linux;
use App\Services\Docker\Dns\OsSupport\MacOs;
use App\Services\Docker\Dns\OsSupport\NullOs;
use App\Services\Docker\Dns\Resolvers\Dhcp;
use App\Services\Docker\Dns\Resolvers\ResolvConf;
use App\Services\Docker\Dns\Resolvers\SystemdResolved;

class FactoryTest extends TestCase {

    private $os;
    private $runner;
    private $filesystem;
    private $collection;

    public function setUp(): void {
        parent::setUp();

        $this->os         = $this->partialMock( OperatingSystem::class );
        $this->runner     = $this->mock( CommandRunner::class );
        $this->filesystem = $this->mock( Filesystem::class );
        $this->collection = new Collection();
    }

    public function test_it_can_create_a_null_resolver() {
        $this->os->shouldReceive( 'getFamily' )->andReturn( 'Windows' );

        $factory = new Factory( $this->os, $this->runner, $this->filesystem );

        $support = $factory->make( $this->collection );

        $this->assertInstanceOf( NullOs::class, $support );
        $this->assertEmpty( $support->resolvers() );
    }

    public function test_it_can_create_an_arch_resolver() {
        $this->os->shouldReceive( 'getFamily' )->andReturn( 'Linux' );
        $this->os->shouldReceive( 'getLinuxFlavor' )->andReturn( 'Arch' );

        $factory = new Factory( $this->os, $this->runner, $this->filesystem  );

        $support = $factory->make( $this->collection );

        $this->assertInstanceOf( Linux::class, $support );
        $this->assertNotEmpty( $support->resolvers() );
        $this->assertSame( 3, $this->collection->count() );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, Dhcp::class );
        } ) );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, SystemdResolved::class );
        } ) );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, ResolvConf::class );
        } ) );
    }

    public function test_it_can_create_a_debian_resolver() {
        $this->os->shouldReceive( 'getFamily' )->andReturn( 'Linux' );
        $this->os->shouldReceive( 'getLinuxFlavor' )->andReturn( 'Debian' );

        $factory = new Factory( $this->os, $this->runner, $this->filesystem  );

        $support = $factory->make( $this->collection );

        $this->assertInstanceOf( Linux::class, $support );
        $this->assertNotEmpty( $support->resolvers() );
        $this->assertSame( 3, $this->collection->count() );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, Dhcp::class );
        } ) );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, SystemdResolved::class );
        } ) );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, ResolvConf::class );
        } ) );
    }

    public function test_it_can_create_an_macos_resolver() {
        $this->os->shouldReceive( 'getFamily' )->andReturn( 'Darwin' );

        $factory = new Factory( $this->os, $this->runner, $this->filesystem );

        $support = $factory->make( $this->collection );

        $this->assertInstanceOf( MacOs::class, $support );
        $this->assertNotEmpty( $support->resolvers() );
        $this->assertSame( 1, $this->collection->count() );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, ResolvConf::class );
        } ) );
    }

}
