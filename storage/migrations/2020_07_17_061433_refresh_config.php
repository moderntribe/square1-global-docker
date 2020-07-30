<?php declare( strict_types=1 );

use App\Services\Migrations\Migration;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh the configuration directory.
 */
final class RefreshConfig extends Migration {

    /**
     * @var \Symfony\Component\Filesystem\Filesystem;
     */
    private $filesystem;

    /**
     * RefreshConfig constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->filesystem = new Symfony\Component\Filesystem\Filesystem();
    }

    /**
     * Run the Migration
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return bool If the migration was successful
     */
    public function up( OutputInterface $output ): bool {
        if ( $this->bypass ) {
            return false;
        }

        $output->writeln( '<question>★ Starting migration!</question>' );

        $configDir = config( 'squareone.config-dir' );
        $global    = $configDir . '/global';

        if ( $this->filesystem->exists( $global ) ) {
            $timestamp = strtotime( 'now' );
            $backup    = $global . '-' . $timestamp . '.backup';

            $output->writeln( sprintf( '<info>★ Backing up data global folder to %s</info>', $backup ) );

            $this->rename( $global, $timestamp );

            $output->writeln( sprintf( '<info>★ Copying new global folder to %s</info>', $global ) );

            $newGlobal = storage_path( 'global' );

            $this->filesystem->mirror( $newGlobal, $global );

            $certs       = $global . '/certs';
            $certsBackup = $backup . '/certs';

            $output->writeln( sprintf( '<info>★ Transferring existing certificates to %s</info>', $certs ) );

            if ( $this->filesystem->exists( $certsBackup ) ) {
                $this->filesystem->mirror( $certsBackup, $certs );
            }

            $output->writeln( '<error>★ IMPORTANT: run so global:stop-all. If you run into database issues, see https://agency.tri.be/wiki/view/SquareOne_Local_Environment#Updating_Database_from_MySQL_to_MariaDB</error>' );
        }

        return true;
    }

    /**
     * Rename a file or directory.
     *
     * @param  string  $path
     * @param  int     $timestamp
     */
    private function rename( string $path, int $timestamp ) {
        if ( $this->filesystem->exists( $path ) ) {
            $this->filesystem->rename( $path, $path . '-' . $timestamp . '.backup' );
        }
    }

}
