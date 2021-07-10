<?php

namespace Tests\Feature\Listeners;

use Tests\TestCase;
use App\Commands\Self\UpdateCheck;

class UpdateCheckListenerTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();

        // Force the listener to fire during tests.
        putenv( 'ALLOW_UPDATE_CHECK=1' );
    }

    public function test_it_fires_update_check_listener() {
        $this->artisan( 'global:stop-all' );
        $this->assertCommandCalled( UpdateCheck::class, [ '--only-new' => true ] );
    }

    public function test_it_does_fires_on_list_command() {
        $this->artisan( 'list' );
        $this->assertCommandNotCalled( UpdateCheck::class, [ '--only-new' => true ] );
    }
}
