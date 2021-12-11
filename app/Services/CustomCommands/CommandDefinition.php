<?php declare(strict_types=1);

namespace App\Services\CustomCommands;

use Spatie\DataTransferObject\FlexibleDataTransferObject;

/**
 * Represents a command as defined in a project's squareone.yml.
 */
class CommandDefinition extends FlexibleDataTransferObject {

    /**
     * The Laravel command signature.
     *
     * @link https://laravel.com/docs/8.x/artisan#defining-input-expectations
     *
     * @var string
     */
    public $signature = '';

    /**
     * The Laravel command description.
     *
     * @var string
     */
    public $description = '';

    /**
     * The command or sequence of commands to run.
     *
     * @var string|array
     */
    public $cmd = '';

    /**
     * The name of the Docker Compose service, as defined in docker-compose.yml.
     *
     * @example php-fpm
     *
     * @var string
     */
    public $service = '';

    /**
     * The user to run commands as in the container.
     *
     * @var string
     */
    public $user = 'squareone';

    /**
     * Allocate a pseudo-TTY.
     *
     * @var bool
     */
    public $tty = true;

    /**
     * Keep STDIN open even if not attached.
     *
     * @var bool
     */
    public $interactive = true;

    /**
     * Set environment variables to pass to the container.
     *
     * @example [ 'VAR' => 1 ]
     *
     * @var array
     */
    public $env = [];

}
