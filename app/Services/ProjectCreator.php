<?php declare( strict_types=1 );

namespace App\Services;

use Illuminate\Filesystem\Filesystem;

/**
 * Aides in creating new projects.
 *
 * @package App\Services
 */
class ProjectCreator {

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * ProjectCreator constructor.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     */
    public function __construct( Filesystem $filesystem ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Set the project's ID.
     *
     * @param  string  $project
     *
     * @return $this
     */
    public function setProjectId( string $project ) {
        $this->filesystem->replace( "{$project}/dev/docker/.projectID", $project );

        return $this;
    }

    /**
     * Update nginx.conf with the proper domain.
     *
     * @param  string  $project
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateNginxConf( string $project ) {
        $file    = "{$project}/dev/docker/nginx/nginx.conf";
        $content = $this->filesystem->get( $file );

        $content = str_replace( 'square1.tribe', "{$project}.tribe", $content );

        $this->filesystem->put( $file, $content );

        return $this;
    }

    /**
     * Update docker-compose.yml with the proper domain.
     *
     * @param  string  $project
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateDockerCompose( string $project ) {
        $file    = "{$project}/dev/docker/docker-compose.yml";
        $content = $this->filesystem->get( $file );

        $content = str_replace( 'square1.tribe', "{$project}.tribe", $content );
        $content = str_replace( 'square1test.tribe', "{$project}test.tribe", $content );
        $content = str_replace( 'tribe_square1', 'tribe_' . str_replace( '-', '_', $project ), $content );

        $this->filesystem->put( $file, $content );

        return $this;
    }

    /**
     * Update WP CLI domain
     *
     * @param  string  $project
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateWpCli( string $project ) {
        $file    = "${project}/dev/docker/wp-cli.yml";
        $content = $this->filesystem->get( $file );

        $content = str_replace( 'square1.tribe', "${project}.tribe", $content );

        $this->filesystem->put( $file, $content );

        return $this;
    }

    /**
     * Update GitHub Workflows
     *
     * @param  string  $project
     *
     * @return $this
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateGitWorkflows( string $project ) {
        $file = "${project}/.github/workflows/ci.yml";

        $content = $this->filesystem->get( $file );
        $content = str_replace( 'tribe_square1', str_replace( '-', '_', "tribe_${project}" ), $content );
        $content = str_replace( 'square1', $project, $content );

        $this->filesystem->put( $file, $content );

        return $this;
    }


    /**
     * Update Codeception .env-dist
     *
     * @param  string  $project
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateCodeceptionConfig( string $project ) {
        $file    = "${project}/dev/tests/.env-dist";
        $content = $this->filesystem->get( $file );

        $content = str_replace( 'square1test.tribe', "${project}test.tribe", $content );
        $content = str_replace( 'tribe_square1', str_replace( '-', '_', "tribe_${project}" ), $content );

        $this->filesystem->put( $file, $content );
        $this->filesystem->copy( $file, str_replace( '-dist', '', $file ) );

        return $this;
    }

    /**
     * Update the codeception dump.sql.
     *
     * @param  string  $project
     *
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateTestDumpSql( string $project ) {
        $file    = "${project}/dev/tests/tests/_data/dump.sql";
        $content = $this->filesystem->get( $file );

        $content = str_replace( 'square1test.tribe', "${project}test.tribe", $content );

        $this->filesystem->put( $file, $content );

        return $this;
    }

}
