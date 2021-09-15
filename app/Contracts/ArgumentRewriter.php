<?php declare( strict_types=1 );

namespace App\Contracts;

/**
 * Constants for use with the ArgumentRewriterTrait to provide
 * proxy arguments due to Symfony console hard coding
 * the --version and -V options.
 *
 * @see \App\Traits\ArgumentRewriterTrait;
 */
interface ArgumentRewriter {

    /**
     * The original version options.
     */
    public const OPTION_VERSION = '--version';

    /**
     * The proxy option that will be rewritten.
     */
    public const OPTION_VERSION_PROXY = '--proxy-version';

    /**
     * The original version flag.
     */
    public const FLAG_VERSION = '-V';

    /**
     * The proxy flag that will be rewritten.
     */
    public const FLAG_VERSION_PROXY = '-1';

    /**
     * Map originals to proxies.
     */
    public const ARGUMENT_MAP = [
        self::OPTION_VERSION => self::OPTION_VERSION_PROXY,
        self::FLAG_VERSION   => self::FLAG_VERSION_PROXY,
    ];

}
