<?php declare( strict_types=1 );

namespace App\Commands\Settings;

use Flintstone\Flintstone;
use App\Commands\BaseCommand;
use App\Services\OperatingSystem;
use App\Services\Nfs\NetworkShare;
use App\Services\Docker\Local\Config;


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
    protected $signature = 'settings:volume';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Change the types of volumes docker uses';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\Nfs\NetworkShare  $networkShare
     * @param  \App\Services\OperatingSystem   $os
     * @param  \Flintstone\Flintstone          $settings
     *
     * @throws \App\Exceptions\SystemExitException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle( NetworkShare $networkShare, OperatingSystem $os, Flintstone $settings ) {
        $menu = $this->menu( 'Select a Docker Volume Type' )
                     ->addOption( 'bind', 'Bind: The default docker volume, 100% performance on Linux' )
                     ->addOption( 'none', 'None: Do not mount the project from the host. All work has to be done inside the container' );

        if ( OperatingSystem::MAC_OS === $os->getFamily() ) {
            $menu = $menu->addOption( 'nfs', 'NFS: Use a Network File Share, up to a 60% performance boost on macOS' )
                         ->addOption( 'mutagen', 'Mutagen: Highly Experimental file sync' );
        }

        $option = $menu->open();

        switch ( $option ) {
            case 'nfs':
                $path    = $this->ask( 'Enter the directory to share. We recommend sharing your /Users folder, but ~/Projects is a good alternative:', '/Users' );
                $confirm = $this->confirm( 'Alright, we\'re going to attempt to automatically configure your share. Enter your sudo password when requested. Ready?' );

                if ( ! $confirm ) {
                    $this->error( 'Cancelled NFS creation' );
                    break;
                }

                $networkShare->add( $path, Config::uid(), Config::gid() );
                $settings->set( 'volume', 'nfs' );

                break;
            case 'none':
                $settings->set( 'volume', 'none' );
                $this->info( 'Using the "none" volume. Warning: Data will be lost if the container is deleted' );
                break;
            case 'mutagen':
                //$answer = $this->confirm( 'This is very experimental and will install a beta version of mutagen with brew. Proceed?' );
                $this->error( 'This feature not yet implemented' );
                break;
            default:
                $this->info( 'Using the default "bind" volume' );
                $settings->set( 'volume', 'bind' );
        }
    }

}
