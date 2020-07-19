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
    protected $dockerComposeFile;

    /**
     * BaseGlobalDocker constructor.
     *
     * @param  string  $dockerComposeFile  The path to the global docker compose file.
     */
    public function __construct( string $dockerComposeFile ) {
        parent::__construct();

        $this->dockerComposeFile = $dockerComposeFile;
    }

}
