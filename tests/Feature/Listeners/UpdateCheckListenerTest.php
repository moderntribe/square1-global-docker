<?php

namespace Tests\Feature\Listeners;

use Tests\TestCase;
use App\Commands\Self\UpdateCheck;

class UpdateCheckListenerTest extends TestCase {

    /**
     * This is failing on MacOS
     */
    public function test_it_fires_update_check_listener() {
        $this->artisan( 'list' );
        $this->assertCommandCalled( UpdateCheck::class, [ '--only-new' => true ] );
    }
}
