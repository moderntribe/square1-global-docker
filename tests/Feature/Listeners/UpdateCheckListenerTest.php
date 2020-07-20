<?php

namespace Tests\Feature\Listeners;

use Tests\TestCase;
use App\Commands\Self\UpdateCheck;

class UpdateCheckListenerTest extends TestCase {

    public function setUp(): void {
        parent::setUp();

        // Force the listener to fire during tests.
        putenv( 'ALLOW_UPDATE_CHECK=1' );
    }

    public function test_it_fires_update_check_listener() {
        $this->artisan( 'list' );
        $this->assertCommandCalled( UpdateCheck::class, [ '--only-new' => true ] );
    }
}
