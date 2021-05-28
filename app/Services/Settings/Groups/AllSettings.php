<?php declare( strict_types=1 );

namespace App\Services\Settings\Groups;

use App\Services\Settings\SettingsWriter;
use Spatie\DataTransferObject\FlexibleDataTransferObject;

/**
 * Get all settings and settings groups.
 *
 * @package App\Services\Settings\Groups
 */
class AllSettings extends FlexibleDataTransferObject {

    public Secrets $secrets;
    public Docker $docker;

    protected SettingsWriter $writer;

    /**
     * Override default DTO functionality to set expected
     * defaults.
     *
     * @param  array                                  $parameters
     * @param  \App\Services\Settings\SettingsWriter  $writer
     */
    public function __construct( SettingsWriter $writer, array $parameters = [] ) {
        $this->writer = $writer;

        $validators = $this->getFieldValidators();

        foreach ( $validators as $field => $validator ) {
            if (
                ! isset( $parameters[ $field ] )
                && ! $validator->isNullable
            ) {
                $class = current( $validator->allowedTypes );

                if ( class_exists( $class ) ) {
                    $parameters[ $field ] = new $class;
                }
            }
        }

        parent::__construct( $parameters );
    }

    public function save() {
        $this->writer->save( $this );
    }

    public function writer(): SettingsWriter {
        return $this->writer;
    }

}
