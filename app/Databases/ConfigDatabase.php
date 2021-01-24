<?php declare( strict_types=1 );

namespace App\Databases;

use Filebase\Database;

/**
 * Load user configuration
 *
 * Stored in ~/.config/store/config
 *
 * @package App\Databases
 */
class ConfigDatabase extends Database {

    /**
     * Filebase database names.
     */
    public const SECRETS = 'user_secrets';
    public const GLOBAL  = 'global';

}
