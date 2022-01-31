<?php declare( strict_types=1 );

namespace App\Traits;

use App\Contracts\ArgumentRewriter;
use App\Input\ParameterManager;

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
     * @param  \App\Input\ParameterManager  $input
     *
     * @return void
     */
    protected function restoreVersionArguments( ParameterManager $input ): void {
        $input->replaceMany( [
            ArgumentRewriter::OPTION_VERSION_PROXY => ArgumentRewriter::OPTION_VERSION,
            ArgumentRewriter::FLAG_VERSION_PROXY   => ArgumentRewriter::FLAG_VERSION,
        ] );
    }

}
