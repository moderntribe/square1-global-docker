<?php declare(strict_types=1);

namespace App\Commands\LocalDocker;

use App\Commands\BaseCommand;
use App\Services\Nfs\NetworkShare;

/**
 * Manages docker volumes
 *
 * @package App\Commands\LocalDocker
 */
class Volume extends BaseCommand {

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'volume {action? : on|off}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Change the types of volumes docker uses';

    public function handle( NetworkShare $networkShare ) {
        $dir = '/Users';
        $networkShare->add( $dir );
    }

}
