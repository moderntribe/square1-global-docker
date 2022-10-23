<?php declare(strict_types=1);

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\Docker;
use App\Commands\LocalDocker\ExportTestDb;
use Illuminate\Support\Facades\Artisan;

final class ExportTestDbTest extends LocalDockerCommand {

    public function test_it_exports_the_database_with_the_default_configuration(): void {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getWorkDir' )->andReturn( '/application/www' );

        $this->container->shouldReceive( 'getId' )->twice()->andReturn( 'php-tests' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--workdir',
            '/application/www',
            'php-tests',
            'wp',
            'core',
            'update-db',
        ] );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--workdir',
            '/application/www',
            'php-tests',
            'wp',
            'db',
            'export',
            '--add-drop-table',
            '/application/www/dev/tests/tests/_data/dump.sql'
        ] );

        Artisan::swap( $this->docker );

        $command = $this->app->make( ExportTestDb::class );

        $tester = $this->runCommand( $command );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( sprintf( 'Performing "wp core update-db" on test database for project %s', $this->project ), $tester->getDisplay() );
        $this->assertStringContainsString( sprintf( 'Exporting test database for project %s to /application/www/dev/tests/tests/_data/dump.sql', $this->project ), $tester->getDisplay() );
    }

    public function test_it_exports_the_database_with_custom_configuration(): void {
        $this->config->shouldReceive( 'getProjectName' )->andReturn( $this->project );
        $this->config->shouldReceive( 'getDockerDir' )->andReturn( $this->dockerDir );
        $this->config->shouldReceive( 'getWorkDir' )->andReturn( '/application/www' );

        $this->container->shouldReceive( 'getId' )->twice()->andReturn( 'custom-docker-container' );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--workdir',
            '/application/www',
            'custom-docker-container',
            'wp',
            'core',
            'update-db',
        ] );

        $this->docker->shouldReceive( 'call' )->with( Docker::class, [
            'exec',
            '--interactive',
            '--tty',
            '--workdir',
            '/application/www',
            'custom-docker-container',
            'wp',
            'db',
            'export',
            '--add-drop-table',
            '/application/www/tests/dump.sql'
        ] );

        Artisan::swap( $this->docker );

        $command = $this->app->make( ExportTestDb::class );

        $tester = $this->runCommand( $command, [
            '--output-path' => '/application/www/tests/dump.sql',
            '--container'   => 'custom-docker-container',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( sprintf( 'Performing "wp core update-db" on test database for project %s', $this->project ), $tester->getDisplay() );
        $this->assertStringContainsString( sprintf( 'Exporting test database for project %s to /application/www/tests/dump.sql', $this->project ), $tester->getDisplay() );
    }

}
