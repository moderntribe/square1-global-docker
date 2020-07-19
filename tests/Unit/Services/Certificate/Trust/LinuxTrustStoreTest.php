<?php

namespace Tests\Unit\Services\Certificate\Trust;

use App\Services\Certificate\Trust\LinuxTrustStore;
use Tests\TestCase;

class LinuxTrustStoreTest extends TestCase {

    public function test_it_configures_trust_store() {
        $directory = storage_path( 'tests/truststores' );
        $filename  = storage_path( 'tests/truststores/%s.crt' );
        $command   = 'the truth is out there';

        $store = new LinuxTrustStore( $directory, $filename, $command );

        $this->assertSame( $directory, $store->directory() );
        $this->assertSame( $filename, $store->filename() );
        $this->assertSame( $command, $store->command() );
    }

}
