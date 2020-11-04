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
use App\Services\Docker\Dns\Resolvers\Scutil;
use App\Services\Docker\Dns\Resolvers\ResolvConf;
use App\Services\Docker\Dns\Resolvers\Openresolv;
use App\Services\Docker\Dns\Resolvers\SystemdResolved;

class FactoryTest extends TestCase {

    private $os;
    private $runner;
    private $filesystem;
    private $collection;

    protected function setUp(): void {
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

    public function test_it_creates_linux_resolvers() {
        $factory = new Factory( $this->os, $this->runner, $this->filesystem );

        $support = $factory->make( $this->collection );

        $this->assertInstanceOf( Linux::class, $support );
        $this->assertNotEmpty( $support->resolvers() );
        $this->assertSame( 4, $this->collection->count() );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, Dhcp::class );
        } ) );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, SystemdResolved::class );
        } ) );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, ResolvConf::class );
        } ) );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, Openresolv::class );
        } ) );
    }

    public function test_it_can_create_an_scutil_resolver() {
        $this->os->shouldReceive( 'getFamily' )->andReturn( 'Darwin' );

        $factory = new Factory( $this->os, $this->runner, $this->filesystem );

        $support = $factory->make( $this->collection );

        $this->assertInstanceOf( MacOs::class, $support );
        $this->assertNotEmpty( $support->resolvers() );
        $this->assertSame( 1, $this->collection->count() );

        $this->assertTrue( $this->collection->contains( function ( $instance ) {
            return is_a( $instance, Scutil::class );
        } ) );
    }

}
