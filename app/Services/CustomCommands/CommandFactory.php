<?php declare(strict_types=1);

namespace App\Services\CustomCommands;

/**
 * Create Command Definitions.
 */
class CommandFactory {

    public const COMMAND_PREFIX = 'project:';

    /**
     * The commands as parsed via squareone.yml.
     *
     * @var array
     */
    protected $commands;

    public function __construct( array $commands ) {
        $this->commands = $commands;
    }

    /**
     * Create a command collection.
     *
     * @return \App\Services\CustomCommands\CommandCollection
     */
    public function make(): CommandCollection {
        $collection = [];

        foreach ( $this->commands as $command ) {
            if ( empty( $command['signature'] ) || empty( $command['cmd'] ) ) {
                continue;
            }

            $command = array_merge( $command, [
                // Prefix custom commands
                'signature' => sprintf( '%s%s', self::COMMAND_PREFIX, $command['signature'] ),
            ] );

            $collection[] = new CommandDefinition( $command );
        }

        return new CommandCollection( $collection );
    }

}
