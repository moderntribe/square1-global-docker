<?php declare( strict_types=1 );

namespace App\Traits;

use App\Contracts\ArgumentRewriter;

/**
 * Symfony Console has hard coded --version and -V options,
 * so they cannot be proxied to other commands as they are
 * hijacked by the main process.
 *
 * This will rename the option before passing it onto
 * the DockerCompose command where it will be renamed
 * back.
 *
 * @example so composer -- --version
 *
 * @see \App\Contracts\ArgumentRewriter;
 */
trait ArgumentRewriterTrait {

    /**
     * Replace `--version` options with `--proxy-version` and
     * '-V' with '-1'.
     *
     * @param  string[]  $args The command arguments and options.
     *
     * @return string[]
     */
    protected function rewriteVersionArguments( array $args ): array {
        return array_map( static function( string $argument ) {
            return ArgumentRewriter::ARGUMENT_MAP[ $argument ] ?? $argument;
        }, $args );
    }

    /**
     * Restore the original version options/flags.
     *
     * @param  string  $command  The full command with all arguments/options.
     *
     * @return string
     */
    protected function restoreVersionArguments( string $command ): string {
        $args         = explode( ' ', $command );
        $argument_map = array_flip( ArgumentRewriter::ARGUMENT_MAP );

        $args = array_map( static function ( string $argument ) use ( $argument_map ) {
            return $argument_map[ $argument ] ?? $argument;
        }, $args );

        return implode( ' ', $args );
    }

}
