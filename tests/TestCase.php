<?php

namespace Tests;

use Exception;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void {
        parent::setUp();
    }

    protected function tearDown(): void {
        parent::tearDown();

        try {
            Storage::disk( 'local' )->deleteDirectory( 'tests' );
        } catch ( Exception $e ) {

        }
    }

}
