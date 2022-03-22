<?php declare( strict_types=1 );

namespace Tests\Feature\Commands\LocalDocker;

use App\Commands\LocalDocker\MigrateDomain;
use App\Commands\LocalDocker\Wp;
use App\Exceptions\DockerException;
use App\Exceptions\SystemExitException;
use App\Recorders\ResultRecorder;
use Exception;
use Illuminate\Support\Facades\Artisan;
use LaravelZero\Framework\Commands\Command;

final class MigrateDomainTest extends LocalDockerCommand {

    private $wpCommand;
    private $recorder;

    protected function setUp(): void {
        parent::setUp();

        $this->wpCommand = $this->mock( Wp::class );
        $this->recorder  = $this->mock( ResultRecorder::class );
    }

    public function test_it_calls_migrate_domain_command() {
        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'prefix',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'first' )->andReturn( 'tribe_' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'query',
                                "SELECT option_value FROM tribe_options WHERE option_name = 'siteurl'",
                                '--skip-column-names',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'offsetGet' )->with( 1 )->andReturn( 'https://test.com' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args' => [
                                'db',
                                'query',
                                "UPDATE tribe_options SET option_value = REPLACE( option_value, 'test.com', 'test.tribe' ) WHERE option_name = 'siteurl'",
                            ],
                        ] );

        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'test.tribe' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args' => [
                                'search-replace',
                                'test.com',
                                'test.tribe',
                                '--all-tables-with-prefix',
                                '--verbose',
                            ],
                        ] );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args' => [
                                'cache',
                                'flush',
                            ],
                        ] );

        Artisan::swap( $this->wpCommand );

        $command = $this->app->make( MigrateDomain::class );

        $tester = $this->runCommand( $command, [], [
            'Ready to search and replace "https://test.com" to "https://test.tribe" (This cannot be undone)?' => 'yes',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
        $this->assertStringContainsString( 'Done.', $tester->getDisplay() );
    }

    public function test_it_throws_an_exception_on_invalid_site_url() {
        $this->expectException( Exception::class );
        $this->expectExceptionMessage( 'Invalid siteurl found in options table:' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'prefix',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'first' )->andReturn( 'tribe_' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'query',
                                "SELECT option_value FROM tribe_options WHERE option_name = 'siteurl'",
                                '--skip-column-names',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'offsetGet' )->with( 1 )->andReturn( '' );

        Artisan::swap( $this->wpCommand );

        $command = $this->app->make( MigrateDomain::class );

        $this->runCommand( $command, [], [
            'Ready to search and replace "https://test.com" to "https://test.tribe" (This cannot be undone)?' => 'yes',
        ] );
    }

    public function test_it_throws_an_exception_on_matching_domains() {
        $this->expectException( Exception::class );
        $this->expectExceptionMessage( 'Error: Source and target domains match:' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'prefix',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'first' )->andReturn( 'tribe_' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'query',
                                "SELECT option_value FROM tribe_options WHERE option_name = 'siteurl'",
                                '--skip-column-names',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'offsetGet' )->with( 1 )->andReturn( 'https://test.tribe' );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'test.tribe' );

        Artisan::swap( $this->wpCommand );

        $command = $this->app->make( MigrateDomain::class );

        $this->runCommand( $command, [], [
            'Ready to search and replace "https://test.com" to "https://test.tribe" (This cannot be undone)?' => 'yes',
        ] );
    }

    public function test_it_throws_an_exception_on_no_confirmation() {
        $this->expectException( SystemExitException::class );
        $this->expectExceptionMessage( 'Cancelling' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'prefix',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'first' )->andReturn( 'tribe_' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'query',
                                "SELECT option_value FROM tribe_options WHERE option_name = 'siteurl'",
                                '--skip-column-names',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'offsetGet' )->with( 1 )->andReturn( 'https://test.com' );
        $this->config->shouldReceive( 'getProjectDomain' )->andReturn( 'test.tribe' );

        Artisan::swap( $this->wpCommand );

        $command = $this->app->make( MigrateDomain::class );

        $this->runCommand( $command, [], [
            'Ready to search and replace "https://test.com" to "https://test.tribe" (This cannot be undone)?' => 'no',
        ] );
    }

    public function test_it_throws_an_exception_on_db_prefix_docker_error() {
        $this->expectException( DockerException::class );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'prefix',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] )->andReturn( Command::FAILURE );

        $this->recorder->shouldReceive( 'first' )->andReturn( 'Error: No such container:' );

        Artisan::swap( $this->wpCommand );

        $command = $this->app->make( MigrateDomain::class );

        $this->runCommand( $command, [], [
            'Ready to search and replace "https://test.com" to "https://test.tribe" (This cannot be undone)?' => 'yes',
        ] );
    }

    public function test_it_throws_an_exception_on_domain_site_url() {
        $this->expectException( DockerException::class );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'prefix',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] );

        $this->recorder->shouldReceive( 'first' )->andReturn( 'tribe_' );

        $this->wpCommand->shouldReceive( 'call' )
                        ->with( Wp::class, [
                            'args'    => [
                                'db',
                                'query',
                                "SELECT option_value FROM tribe_options WHERE option_name = 'siteurl'",
                                '--skip-column-names',
                            ],
                            '--notty' => true,
                            '--quiet' => true,
                        ] )->andReturn( Command::FAILURE );

        $this->recorder->shouldReceive( 'offsetGet' )->with( 1 )->andReturn( 'Error: No such container:' );

        Artisan::swap( $this->wpCommand );

        $command = $this->app->make( MigrateDomain::class );

        $this->runCommand( $command, [], [
            'Ready to search and replace "https://test.com" to "https://test.tribe" (This cannot be undone)?' => 'yes',
        ] );
    }

}
