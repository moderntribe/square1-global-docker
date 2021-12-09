<?php declare( strict_types=1 );

namespace App\Services;

use App\Commands\Docker;
use App\Contracts\ArgumentRewriter;
use App\Services\Docker\Container;
use Composer\Semver\Comparator;
use Illuminate\Support\Facades\Artisan;

/**
 * Composer version detection in containers.
 */
class ComposerVersion {

    public const MINIMUM_VERSION = 2;

    /**
     * @var \App\Services\Docker\Container
     */
    protected $container;

    public function __construct( Container $container ) {
        $this->container = $container;
    }

    public function isVersionOne(): bool {
        Artisan::call( Docker::class, [
            'exec',
            $this->container->getId(),
            'composer',
            ArgumentRewriter::OPTION_VERSION_PROXY,
        ] );

        $output       = Artisan::output();
        $versionRegex = '/(?P<version>\d+\.\d+\.\d+)/';

        preg_match( $versionRegex, $output, $matches );

        $version = $matches[ 'version' ] ?? 0;

        // Project isn't using composer
        if ( empty( $version ) ) {
            return false;
        }

        return Comparator::lessThan( $version, self::MINIMUM_VERSION );
    }

}
