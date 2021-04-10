<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\FileIO;
use App\Services\XdebugValidator;

class XdebugValidatorTest extends TestCase {

    private $file;
    private $path;

    protected function setUp(): void {
        parent::setUp();

        $this->file = $this->mock( FileIO::class );
        $this->path = storage_path( 'tests/dev/docker/php/php-ini-overrides.ini' );
    }

    public function test_it_finds_a_valid_php_ini_file() {
        $validator = new XdebugValidator( $this->file );

        $this->file->expects( 'exists' )->once()->with( $this->path )->andReturnTrue();
        $this->file->expects( 'contains' )->once()->with( $this->path, 'xdebug.mode' )->andReturnTrue();

        $this->assertTrue( $validator->valid( $this->path ) );
    }

    public function test_it_returns_true_if_old_squareone_project() {
        $validator = new XdebugValidator( $this->file );

        $this->file->expects( 'exists' )->once()->with( $this->path )->andReturnFalse();

        $this->assertTrue( $validator->valid( $this->path ) );
    }

    public function test_it_find_invalid_php_ini_configuration() {
        $validator = new XdebugValidator( $this->file );

        $this->file->expects( 'exists' )->once()->with( $this->path )->andReturnTrue();
        $this->file->expects( 'contains' )->once()->with( $this->path, 'xdebug.mode' )->andReturnFalse();

        $this->assertFalse( $validator->valid( $this->path ) );
    }

}
