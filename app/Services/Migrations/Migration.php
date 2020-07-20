<?php declare( strict_types=1 );

namespace App\Services\Migrations;

/**
 * Class Migration
 *
 * @package App\Services\Migrations
 */
abstract class Migration {

    /**
     * Bypass running a migration.
     *
     * @var bool
     */
    protected $bypass = false;

    /**
     * Migration constructor.
     */
    public function __construct() {
        // Don't run during tests unless specified with the TEST_BYPASS environment variable.
        if ( 'testing' === env( 'APP_ENV' ) && '1' != env( 'ALLOW_MIGRATION' ) ) {
            $this->bypass = true;
        }
    }

    /**
     * Run the Migration
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return bool If the migration was successful
     */
    abstract public function up( \Symfony\Component\Console\Output\OutputInterface $output ): bool;

}
