<?php

namespace Tests\Unit\Services\Docker\Dns\OsSupport;

use Tests\TestCase;
use App\Services\Docker\Dns\OsSupport\Linux;
use App\Services\Docker\Dns\OsSupport\MacOs;
use App\Services\Docker\Dns\OsSupport\NullOs;

class OsSupportTest extends TestCase {

    public function test_it_has_linux_support() {
        $os = $this->app->make( Linux::class );

        $this->assertTrue( $os->supported() );
    }

    public function test_it_has_macos_support() {
        $os = $this->app->make( MacOs::class );

        $this->assertTrue( $os->supported() );
    }

    public function test_unknown_os_is_not_supported() {
        $os = $this->app->make( NullOs::class );

        $this->assertFalse( $os->supported() );
    }
}
