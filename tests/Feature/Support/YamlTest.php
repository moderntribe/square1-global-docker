<?php

namespace Tests\Feature\Support;

use Tests\TestCase;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Storage;

class YamlTest extends TestCase {

    /** @var \App\Support\Yaml $yaml */
    private $yaml;

    protected function setUp(): void {
        parent::setUp();

        Storage::put( 'tests/yaml/squareone.yml', Yaml::dump( [
            'item' => [
                'item-1' => 'Item 1',
                'item-2' => 'Item 2',
                'item-3' => 'Item 3',
                'item-4' => 'Item 4',
                'sub-item' => [
                    'sub-item-1' => 'Sub Item 1',
                    'sub-item-2' => 'Sub Item 2',
                    'sub-item-3' => 'Sub Item 3',
                ]
            ],
        ] ) );

        Storage::put( 'tests/yaml/override/squareone.yml', Yaml::dump( [
            'item' => [
                'item-3' => 'Overridden Item 3',
                'sub-item' => [
                    'sub-item-2' => 'Overridden Item 2'
                ]
            ],
        ] ) );

        $this->yaml = $this->app->make( \App\Support\Yaml::class );
    }

    public function testItOverridesConfigurationValues() {
        $files = [
            storage_path( 'tests/yaml/squareone.yml' ),
            storage_path( 'tests/yaml/override/squareone.yml' ),
        ];

        $config = $this->yaml->loadToConfig( $files, 'squareone' );

       $this->assertSame( [
           'item' => [
               'item-1' => 'Item 1',
               'item-2' => 'Item 2',
               'item-3' => 'Overridden Item 3',
               'item-4' => 'Item 4',
               'sub-item' => [
                   'sub-item-1' => 'Sub Item 1',
                   'sub-item-2' => 'Overridden Item 2',
                   'sub-item-3' => 'Sub Item 3',
               ]
           ],
       ], $config->toArray() );
    }

    public function testItReverseOverridesConfigurationValues() {
        // Reversed
        $files = [
            storage_path( 'tests/yaml/override/squareone.yml' ),
            storage_path( 'tests/yaml/squareone.yml' ),
        ];

        $config = $this->yaml->loadToConfig( $files, 'squareone' );

        $this->assertSame( [
            'item' => [
                'item-1' => 'Item 1',
                'item-2' => 'Item 2',
                'item-3' => 'Item 3',
                'item-4' => 'Item 4',
                'sub-item' => [
                    'sub-item-1' => 'Sub Item 1',
                    'sub-item-2' => 'Sub Item 2',
                    'sub-item-3' => 'Sub Item 3',
                ]
            ],
        ], $config->toArray() );
    }

    public function testItCombinesValues() {
        Storage::put( 'tests/yaml-compare/squareone.yml', Yaml::dump( [
            'item' => [
                'item-1' => 'Item 1',
                'sub' => [
                    'item-2' => 'Item 2',
                ],
            ],
        ] ) );

        Storage::put( 'tests/yaml-compare/override/squareone.yml', Yaml::dump( [
            'item' => [
                'item-2' => 'Item 2',
                'sub' => [
                    'item-1' => 'Item 1',
                ],
            ],
        ] ) );

        $files = [
            storage_path( 'tests/yaml-compare/squareone.yml' ),
            storage_path( 'tests/yaml-compare/override/squareone.yml' ),
        ];

        $config = $this->yaml->loadToConfig( $files, 'squareone' );

        $this->assertSame( [
            'item' => [
                'item-1' => 'Item 1',
                'item-2' => 'Item 2',
                'sub' => [
                    'item-1' => 'Item 1',
                    'item-2' => 'Item 2',
                ]
            ],
        ], $config->toArray() );
    }

}
