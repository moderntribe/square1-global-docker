<?php declare( strict_types=1 );

namespace App\Services\Settings;

use Throwable;
use Laminas\Config\Reader\ReaderInterface;

class SettingsLoader {

    protected ReaderInterface $reader;

    public function __construct( ReaderInterface $reader ) {
        $this->reader = $reader;
    }

    public function load( string $file ): array {
        try {
            $settings = $this->reader->fromFile( $file );
        } catch ( Throwable $e ) {
            $settings = [];
        }

        return $settings;
    }

}
