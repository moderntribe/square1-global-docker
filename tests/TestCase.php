<?php

namespace Tests;

use Exception;
use App\Bootstrap;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setUp(): void {
        parent::setUp();

        // Bootstrap SO
        $bootstrap = $this->app->make( Bootstrap::class );
        $bootstrap->boot();
    }

    public function tearDown(): void {
        parent::tearDown();

        try {
            Storage::disk( 'local' )->deleteDirectory( 'tests' );
        } catch ( Exception $e ) {

        }
    }

}
