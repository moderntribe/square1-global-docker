<?php

namespace Tests;

use Exception;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void {
        parent::setUp();

        // Initialize Yaml config after the application is booted
        $provider = new AppServiceProvider( $this->app );
        $provider->initConfig();
    }

    protected function tearDown(): void {
        $config = app( 'config' );

        parent::tearDown();

        app()->instance( 'config', $config );

        try {
            Storage::disk( 'local' )->deleteDirectory( 'tests' );
        } catch ( Exception $e ) {

        }
    }

}
