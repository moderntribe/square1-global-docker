<?php declare( strict_types=1 );

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

/**
 * Extend when operating system commands need to be run within the console commands.
 *
 * @method \NunoMaduro\LaravelConsoleMenu\Menu menu( string $title, string[] $options )
 *
 * @package App\Commands
 */
abstract class BaseCommand extends Command {

    /**
     * Console success code
     */
    public const EXIT_SUCCESS = 0;

    /**
     * Console error code
     */
    public const EXIT_ERROR = 1;

    /**
     * Environment variable to enable XDEBUG 3.0
     */
    public const XDEBUG_ENV = 'XDEBUG_SESSION=PHPSTORM';

}
