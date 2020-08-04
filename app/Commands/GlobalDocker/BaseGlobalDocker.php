<?php declare( strict_types=1 );

namespace App\Commands\GlobalDocker;

use App\Commands\BaseCommand;

/**
 * Abstract for Global Docker commands
 *
 * @package App\Commands\GlobalDocker
 */
abstract class BaseGlobalDocker extends BaseCommand {

    public const PROJECT_NAME = 'global';

    /**
     * The path to the global docker compose file.
     *
     * @var string
     */
    protected $globalDirectory;

    /**
     * BaseGlobalDocker constructor.
     *
     * @param  string  $globalDirectory  The path to the global docker directory.
     */
    public function __construct( string $globalDirectory ) {
        parent::__construct();

        $this->globalDirectory = $globalDirectory;
    }

}
