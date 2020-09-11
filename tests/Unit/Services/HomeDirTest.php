<?php

namespace Tests\Unit\Services;

use App\Services\HomeDir;
use Tests\TestCase;

class HomeDirTest extends TestCase {

    public function test_it_finds_a_windows_homedir() {
        putenv( 'HOME=' );

        $_SERVER['HOMEDRIVE'] = 'c:/';
        $_SERVER['HOMEPATH'] = 'tmp';

        $homeDir = ( new HomeDir() )->get();

        $this->assertEquals( 'c:/tmp', $homeDir );
    }

    public function test_it_finds_homedir_based_on_env() {
        $home = putenv( 'HOME=/tmp' );

        $this->assertTrue( $home );

        $homeDir = ( new HomeDir() )->get();

        $this->assertEquals( '/tmp', $homeDir );
    }

}
