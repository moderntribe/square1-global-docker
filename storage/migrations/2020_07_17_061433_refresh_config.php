<?php declare( strict_types=1 );

use App\Services\Migrations\Migration;

/**
 * Refresh the configuration directory.
 */
final class RefreshConfig extends Migration {

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * RefreshConfig constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->filesystem = new Illuminate\Filesystem\Filesystem();
    }

    /**
     * Run the Migration
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return bool If the migration was successful
     */
    public function up( \Symfony\Component\Console\Output\OutputInterface $output ): bool {
        if ( $this->bypass ) {
            return false;
        }

        $output->writeln( '<question>★ Starting migration!</question>' );

        $configDir     = config( 'squareone.config-dir' );
        $squareoneYml  = $configDir . '/squareone.yml';
        $dockerCompose = $configDir . '/global/docker-compose.yml';
        $override      = $configDir . '/global/docker-compose.override.yml';
        $mysql         = $configDir . '/global/mysql/mysql.cnf';

        $output->writeln( sprintf( '<info>★ Backing up data in %s</info>', $configDir ) );

        $this->backUpFile( $squareoneYml );
        $this->backUpFile( $dockerCompose );
        $this->backUpFile( $override );
        $this->backUpFile( $mysql );

        if ( $this->filesystem->exists( $configDir . '/global') ) {
            $output->writeln( sprintf( '<info>★ Copying updated %s</info>', $dockerCompose ) );

            $this->filesystem->copy( storage_path( 'global/docker-compose.yml' ), $dockerCompose );

            $output->writeln( sprintf( '<info>★ Copying updated %s</info>', $mysql ) );

            return (bool) $this->filesystem->copy( storage_path( 'global/mysql/mysql.cnf' ), $mysql );
        }

        return false;
    }

    private function backUpFile( string $path ) {
        $timestamp = strtotime( 'now' );

        if ( $this->filesystem->exists( $path ) ) {
            $this->filesystem->move( $path, $path . '-' . $timestamp . '.backup' );
        }
    }

}
