<?php

namespace Tests\Unit\Listeners;

use App\Listeners\UpdateCheckListener;
use Illuminate\Console\Events\CommandFinished;
use Tests\TestCase;

class UpdateCheckListenerTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();

        // Force the listener to fire during tests.
        putenv( 'ALLOW_UPDATE_CHECK=1' );
    }

    public function test_it_returns_false_when_no_command_is_provided() {
        $listener = new UpdateCheckListener();
        $event    = $this->mock( CommandFinished::class );

        $this->assertFalse( $listener->handle( $event ) );
    }

}
