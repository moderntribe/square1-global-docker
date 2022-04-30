<?php declare(strict_types=1);

use App\Services\Migrations\Migration;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateGlobalComposeNginxImage extends Migration {

    /**
     * @var \Symfony\Component\Filesystem\Filesystem;
     */
    private $filesystem;

    public function __construct() {
        parent::__construct();

        $this->filesystem = new Symfony\Component\Filesystem\Filesystem();
    }

    /**
     * Run the Migration.
     *
     * @param OutputInterface $output
     *
     * @return bool If the migration was successful
     */
    public function up( OutputInterface $output ): bool {
        if ( $this->bypass ) {
            return false;
        }

        $output->writeln( '<question>★ Updating global docker-compose.yml...</question>' );

        $file = config( 'squareone.docker.config-dir' ) . '/docker-compose.yml';

        if ( ! $this->filesystem->exists( $file ) ) {
            return true;
        }

        $timestamp = strtotime( 'now' );
        $backup    = str_replace( '.yml', '.yml.' . $timestamp . '.backup', $file );

        $output->writeln( sprintf( '<info>★ Backing up global docker-compose.yml to %s</info>', $backup ) );

        $this->filesystem->rename( $file, $backup );

        $newGlobalCompose = storage_path( 'global/docker-compose.yml' );

        $this->filesystem->copy( $newGlobalCompose, $file, true );

        $output->writeln( '<error>★ IMPORTANT: run so global:stop-all</error>' );

        return true;
    }

}
