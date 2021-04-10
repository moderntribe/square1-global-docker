<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FileIO;
use App\Services\XdebugValidator;

class XdebugValidatorTest extends TestCase {

    private $file;

    protected function setUp(): void {
        parent::setUp();

        $this->file = $this->mock( FileIO::class );
    }

    public function test_it_finds_a_valid_php_ini_file() {
        $path      = storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' );
        $validator = new XdebugValidator( $this->file );

        $this->file->expects( 'exists' )->once()->with( $path )->andReturnTrue();
        $this->file->expects( 'contains' )->once()->with( $path, 'xdebug.mode' )->andReturnTrue();

        $this->assertTrue( $validator->valid( $path ) );
    }

    public function test_it_returns_true_if_old_squareone_project() {
        $path      = storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' );
        $validator = new XdebugValidator( $this->file );

        $this->file->expects( 'exists' )->once()->with( $path )->andReturnFalse();

        $this->assertTrue( $validator->valid( $path ) );
    }

    public function test_it_find_invalid_php_ini_configuration() {
        $path      = storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' );
        $validator = new XdebugValidator( $this->file );

        $this->file->expects( 'exists' )->once()->with( $path )->andReturnTrue();
        $this->file->expects( 'contains' )->once()->with( $path, 'xdebug.mode' )->andReturnFalse();

        $this->assertFalse( $validator->valid( $path ) );
    }

}
