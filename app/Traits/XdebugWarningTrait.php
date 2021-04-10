<?php declare( strict_types=1 );

namespace App\Traits;

trait XdebugWarningTrait {

    protected function outdatedXdebugWarning( string $phpIni ): void {
        $this->warn( sprintf(
            'This project\'s %s is not configured correctly for xdebug v3.0+. See %s upgrade instructions.',
            $phpIni,
            'https://github.com/moderntribe/square-one/pull/695'
        ) );
    }
}
