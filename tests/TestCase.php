<?php declare( strict_types=1 );

namespace Tests;

use Exception;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;
use phpmock\mockery\PHPMockery;

abstract class TestCase extends BaseTestCase {

    use CreatesApplication;

    protected function setUp(): void {
        parent::setUp();

        // https://github.com/php-mock/php-mock-mockery#restrictions
        PHPMockery::define( 'App\Services', 'getmyuid' );
        PHPMockery::define( 'App\Services', 'getmygid' );
    }

    protected function tearDown(): void {
        parent::tearDown();

        try {
            Storage::disk( 'local' )->deleteDirectory( 'tests' );
        } catch ( Exception $e ) {

        }
    }

}
