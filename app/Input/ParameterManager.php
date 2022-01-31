<?php declare( strict_types=1 );

namespace App\Input;

use ReflectionObject;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Manages command parameters (arguments and options).
 */
class ParameterManager {

    /**
     * An array that makes up the entire command, its arguments and options.
     *
     * @var string[]
     */
    protected $parameters;

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     *
     * @throws \ReflectionException
     */
    public function __construct( InputInterface $input ) {
        $this->parameters = $this->getParametersFromInput( $input );
    }

    /**
     * Retrieve the raw command parameters.
     *
     * @return string[]
     */
    public function parameters(): array {
        return $this->parameters;
    }

    /**
     * Attempt to determine the command.
     *
     * @return string
     */
    public function command(): string {
        $command = $this->parameters[ 'command' ] ?? $this->parameters[0];

        return is_array( $command ) ? implode( ' ', $command ) : $command;
    }

    /**
     * Replace the command.
     *
     * @param  string  $command The replacement command.
     *
     * @return void
     */
    public function replaceCommand( string $command ) {
        // Commands with spaces will be escaped by symfony
        if ( str_contains( $command, ' ' ) ) {
            $command = explode( ' ', $command );
        }

        if ( isset( $this->parameters[ 'command' ] ) ) {
            $this->parameters[ 'command' ] = $command;
        } else {
            $this->parameters[0] = $command;
        }
    }

    /**
     * Whether this command has an argument/option or a series of those.
     *
     * @param array $values
     *
     * @return bool
     */
    public function has( array $values ): bool {
        $found = array_intersect( $this->parameters, $values );

        return ! empty( $found );
    }

    /**
     * Add an argument to the parameters.
     *
     * @param  array   $values  The argument(s) to add.
     * @param  string  $after   The value in the array where this argument will be added after.
     *
     * @return bool
     */
    public function add( array $values, string $after ): bool {
        $key = array_search( $after, $this->parameters, true );

        if ( $key === false ) {
            return false;
        }

        $position = $key + 1;

        // Bump the position if this has a command index
        if ( isset( $this->parameters['command'] ) ) {
            $position++;
        }

        // Inject the new values into the position after our found value
        $this->parameters = array_merge(
            array_slice( $this->parameters, 0, $position ),
            $values,
            array_slice( $this->parameters, $position )
        );

        return true;
    }

    /**
     * Replace a parameter in the list.
     *
     * @param  string  $search The parameter to search for.
     * @param  string  $replace The replacement parameter, if $search was found.
     *
     * @return void
     */
    public function replace( string $search, string $replace ) {
        $this->parameters = array_map( static function ( string $argument ) use ( $search, $replace ) {
            return ( $argument === $search ) ? $replace : $argument ;
        }, $this->parameters );
    }

    /**
     * An associative array where keys are the search parameter and the value is the
     * replacement parameter.
     *
     * @param  array  $values
     *
     * @return void
     */
    public function replaceMany( array $values ) {
        $this->parameters = array_map( static function( string $argument ) use ( $values ) {
            return $values[ $argument ] ?? $argument;
        }, $this->parameters );
    }

    /**
     * Returns a stringified representation of the args passed to the command.
     *
     * @return string
     */
    public function __toString(): string {
        return (string) ( new ArrayInput( $this->parameters ) );
    }

    /**
     * Use the reflection API to fetch the underlying $parameters property
     * in the ArrayInput object.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    protected function getParametersFromInput( InputInterface $input ): array {
        $arrayInput = new ReflectionObject( $input );
        $parameters = $arrayInput->getProperty( 'parameters' );
        $parameters->setAccessible( true );

        return $parameters->getValue( $input );
    }

}
