<?php declare( strict_types=1 );

namespace App\Services\Settings;

use Laminas\Config\Writer\WriterInterface;
use App\Services\Settings\Groups\AllSettings;

class SettingsWriter {

    protected WriterInterface $writer;
    protected string $file;

    public function __construct( WriterInterface $writer, string $file ) {
        $this->writer = $writer;
        $this->file   = $file;
    }

    public function save( AllSettings $settings ) {
        $this->writer->toFile( $this->file, $settings->toArray() );
    }

    public function file(): string {
        return $this->file;
    }

}
