<?php declare( strict_types=1 );

namespace Tests\Unit\Services\CustomCommands;

use App\Services\CustomCommands\CommandCollection;
use App\Services\CustomCommands\CommandFactory;
use Tests\TestCase;

final class CommandFactoryTest extends TestCase {

    public function test_it_makes_a_command_collection_with_prefixes() {
        $commands = [
            [
                'signature'   => 'ls',
                'cmd'         => 'ls',
                'description' => 'List directory contents',
                'service'     => 'php-fpm',
                'env'         => [
                    'VAR' => 'value',
                ],
            ],
            [
                'signature'   => 'ls-all',
                'cmd'         => [
                    'ls',
                    'ls -R',
                ],
                'description' => 'List current directory and recursive directories',
                'service'     => 'php-tests',
                'env'         => [
                    'VAR' => 'value',
                    'VA2' => 'value2',
                ],
            ],
        ];

        $factory = new CommandFactory( $commands );

        $collection = $factory->make();

        $this->assertInstanceOf( CommandCollection::class, $collection );

        /**
         * @var $command1 \App\Services\CustomCommands\CommandDefinition
         * @var $command2 \App\Services\CustomCommands\CommandDefinition
         */
        [ $command1, $command2 ] = $collection->items();

        $this->assertSame( 'project:ls', $command1->signature );
        $this->assertSame( 'ls', $command1->cmd );
        $this->assertSame( 'List directory contents', $command1->description );
        $this->assertSame( 'php-fpm', $command1->service );
        $this->assertSame( [
            'VAR' => 'value',
        ], $command1->env );
        $this->assertEmpty( $command1->args );
        $this->assertEmpty( $command1->options );

        $this->assertSame( 'project:ls-all', $command2->signature );
        $this->assertSame( [
            'ls',
            'ls -R',
        ], $command2->cmd );
        $this->assertSame( 'List current directory and recursive directories', $command2->description );
        $this->assertSame( 'php-tests', $command2->service );
        $this->assertSame( [
            'VAR' => 'value',
            'VA2' => 'value2',
        ], $command2->env );
        $this->assertEmpty( $command2->args );
        $this->assertEmpty( $command2->options );
    }

    public function test_it_ignores_invalid_commands() {
        $commands = [
            [
                'cmd'         => 'this will be missing',
                'description' => 'A command missing the signature property',
            ],
            [
                'signature'   => 'ls',
                'cmd'         => 'ls',
                'description' => 'List directory contents',
                'service'     => 'php-fpm',
            ],
            [
                'signature'   => 'broken',
                'description' => 'A command missing the cmd property',
            ]
        ];

        $factory = new CommandFactory( $commands );

        $collection = $factory->make();

        $this->assertInstanceOf( CommandCollection::class, $collection );
        $this->assertSame( 1, $collection->count() );

        [ $command ] = $collection->items();

        $this->assertSame( 'project:ls', $command->signature );
    }
}
