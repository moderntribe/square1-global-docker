<?php declare( strict_types=1 );

namespace App\Services;

use App\Commands\DockerCompose;
use App\Contracts\ArgumentRewriter;
use App\Services\Docker\Local\Config;
use Composer\Semver\Comparator;
use Illuminate\Support\Facades\Artisan;

/**
 * Composer version detection in containers.
 */
class ComposerVersion {

    public const MINIMUM_VERSION = 2;

    public function isVersionOne( Config $config ): bool {
        chdir( $config->getDockerDir() );

        Artisan::call( DockerCompose::class, [
            '--project-name',
            $config->getProjectName(),
            'exec',
            '-T',
            'php-fpm',
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
