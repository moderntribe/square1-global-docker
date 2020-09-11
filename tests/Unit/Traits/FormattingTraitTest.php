<?php

namespace Tests\Unit\Traits;

use App\Traits\FormattingTrait;
use Tests\TestCase;

class FormattingTraitTest extends TestCase {

    private $formatter;

    protected function setUp(): void {
        parent::setUp();

        $this->formatter = $this->getMockForTrait( FormattingTrait::class );
    }

    protected function tearDown(): void {
        parent::tearDown();

        $this->formatter = null;
    }

    public function test_it_formats_text() {
        $output = ' this should be trimmed   ';

        $this->assertNotSame( 'this should be trimmed', $output );
        $this->assertSame( 'this should be trimmed', $this->formatter->formatOutput( $output ) );

    }

}
