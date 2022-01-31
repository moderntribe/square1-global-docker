<?php declare( strict_types=1 );

namespace Tests\Unit\Input;

use App\Input\ParameterManager;
use Symfony\Component\Console\Input\ArrayInput;
use Tests\TestCase;

final class ParameterManagerTest extends TestCase {

    public function test_it_copies_parameters(): void {
        $input = new ArrayInput( [
            'docker',
            'exec',
            '-i',
            'containerId',
            '/bin/bash',
        ] );

        $manager = new ParameterManager( $input );

        $this->assertSame( (string) $input, (string) $manager );
        $this->assertSame( [
            'docker',
            'exec',
            '-i',
            'containerId',
            '/bin/bash',
        ], $manager->parameters() );
    }

    public function test_it_replaces_a_symfony_command(): void {
        $manager = new ParameterManager( new ArrayInput( [
            'command' => 'hello-world',
            '--path',
            '/some/place',
        ] ) );

        $this->assertSame( 'hello-world', $manager->command() );

        $manager->replaceCommand( 'hello there world' );

        $this->assertSame( 'hello there world', $manager->command() );
        $this->assertSame( [
            'command' => [
                'hello',
                'there',
                'world',
            ],
            '--path',
            '/some/place',
        ], $manager->parameters() );

        $this->assertSame( "hello there world --path '/some/place'", (string) $manager );
    }

    public function test_it_replaces_a_laravel_command(): void {
        $manager = new ParameterManager( new ArrayInput( [
            'hello-world',
            '--path',
            '/some/place',
        ] ) );

        $this->assertSame( 'hello-world', $manager->command() );

        $manager->replaceCommand( 'hello there world' );

        $this->assertSame( 'hello there world', $manager->command() );
        $this->assertSame( [
            [
                'hello',
                'there',
                'world',
            ],
            '--path',
            '/some/place',
        ], $manager->parameters() );

        $this->assertSame( "hello there world --path '/some/place'", (string) $manager );
    }

    public function test_it_has_values(): void {
        $manager = new ParameterManager( new ArrayInput( [
            'command' => 'docker',
            'exec',
            '--tty',
            '--interactive',
            '--user',
            'squareone:squareone',
            'containerId',
            '/bin/bash',
        ] ) );

        $this->assertTrue( $manager->has( [ 'docker' ] ) );
        $this->assertTrue( $manager->has( [ 'docker', 'exec' ] ) );
        $this->assertTrue( $manager->has( [ 'exec', 'docker' ] ) );
        $this->assertTrue( $manager->has( [ 'exec', 'docker' ] ) );
        $this->assertTrue( $manager->has( [ 'docker', 'exec', '--user' ] ) );
        $this->assertTrue( $manager->has( [ 'squareone:squareone' ] ) );
        $this->assertTrue( $manager->has( [ '/bin/bash' ] ) );

        // Will return true if any items are found, even with missing items
        $this->assertTrue( $manager->has( [ 'invalid', 'docker', 'exec' ] ) );

        $this->assertFalse( $manager->has( [ 'dock', 'exe', '-user' ] ) );
        $this->assertFalse( $manager->has( [ 'user' ] ) );
        $this->assertFalse( $manager->has( [ '-i' ] ) );
        $this->assertFalse( $manager->has( [ 'command' ] ) );
        $this->assertFalse( $manager->has( [ '' ] ) );
        $this->assertFalse( $manager->has( [ 0 ] ) );
    }

    public function test_it_adds_values(): void {
        $manager = new ParameterManager( new ArrayInput( [
            'command' => 'docker',
            'exec',
            '--tty',
            '--interactive',
            'containerId',
            '/bin/bash',
        ] ) );

        $result = $manager->add( [
            '--user',
            'squareone:squareone',
        ], 'exec' );

        $this->assertTrue( $result );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '--user',
            'squareone:squareone',
            '--tty',
            '--interactive',
            'containerId',
            '/bin/bash',
        ], $manager->parameters() );

        $result = $manager->add( [
            '-d',
        ], '--interactive' );

        $this->assertTrue( $result );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '--user',
            'squareone:squareone',
            '--tty',
            '--interactive',
            '-d',
            'containerId',
            '/bin/bash',
        ], $manager->parameters() );

        $result = $manager->add( [
            '-c',
            '"hey"',
        ], '/bin/bash' );

        $this->assertTrue( $result );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '--user',
            'squareone:squareone',
            '--tty',
            '--interactive',
            '-d',
            'containerId',
            '/bin/bash',
            '-c',
            '"hey"',
        ], $manager->parameters() );

        $result = $manager->add( [
            '--workdir',
            '/application/www',
        ], 'unknownValue' );

        $this->assertFalse( $result );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '--user',
            'squareone:squareone',
            '--tty',
            '--interactive',
            '-d',
            'containerId',
            '/bin/bash',
            '-c',
            '"hey"',
        ], $manager->parameters() );
    }

    public function test_it_replaces_a_value(): void {
        $manager = new ParameterManager( new ArrayInput( [
            'command' => 'docker',
            'exec',
            '--tty',
            '--interactive',
            'containerId',
            '/bin/bash',
        ] ) );

        $manager->replace( '--interactive', '--privileged' );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '--tty',
            '--privileged',
            'containerId',
            '/bin/bash',
        ], $manager->parameters() );

        $manager->replace( 'containerId', 'newContainerId' );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '--tty',
            '--privileged',
            'newContainerId',
            '/bin/bash',
        ], $manager->parameters() );

        $this->assertSame(
            "docker exec --tty --privileged newContainerId '/bin/bash'",
            (string) $manager
        );
    }

    public function test_it_replace_values(): void {
        $manager = new ParameterManager( new ArrayInput( [
            'command' => 'docker',
            'exec',
            '--interactive',
            'containerId',
            'composer',
            '--version',
        ] ) );

        $manager->replaceMany( [
            '--interactive' => '-i',
            '--version'     => '--proxy-version',
        ] );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '-i',
            'containerId',
            'composer',
            '--proxy-version',
        ], $manager->parameters() );

        $manager->replaceMany( [
            '-i'              => '--interactive',
            '--proxy-version' => '--version',
            'composer'        => '/usr/bin/composer',
        ] );

        $this->assertSame( [
            'command' => 'docker',
            'exec',
            '--interactive',
            'containerId',
            '/usr/bin/composer',
            '--version',
        ], $manager->parameters() );

        $this->assertSame(
            "docker exec --interactive containerId '/usr/bin/composer' --version",
            (string) $manager
        );
    }

}
