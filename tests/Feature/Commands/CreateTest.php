<?php declare(strict_types=1);

namespace Tests\Feature\Commands;

use App\Commands\Create;
use App\Commands\LocalDocker\Bootstrap;
use App\Exceptions\SystemExitException;
use App\Runners\CommandRunner;
use App\Services\ProjectCreator;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Artisan;
use phpmock\mockery\PHPMockery;

final class CreateTest extends BaseCommandTester {

    private $projectCreator;
    private $runner;

    protected function setUp(): void {
        parent::setUp();

        PHPMockery::mock( '\App\Commands', 'chdir' )->andReturnTrue();

        $this->projectCreator = $this->mock( ProjectCreator::class );
        $this->runner         = $this->mock( CommandRunner::class );
    }

    public function test_it_creates_a_project(): void {
        $this->projectCreator->shouldReceive( 'setProjectId' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateNginxConf' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateDockerCompose' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateWpCli' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateGitWorkflows' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateCodeceptionConfig' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateTestDumpSql' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->once()->with( [
            'directory' => 'test-project',
            'remote'    => 'https://github.com/moderntribe/test-project',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->once()->with( [
            'directory' => 'test-project',
            'branch'    => 'main',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->once()->with( [
            'directory' => 'test-project',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'enableTty' )
                     ->times( 4 )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'git -C {{ $directory }} checkout {{ $branch }}' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'git -C {{ $directory }} branch -m develop' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'git -C {{ $directory }} remote set-url origin {{ $remote }}' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [
                         'repo'      => 'https://github.com/moderntribe/square-one',
                         'directory' => 'test-project',
                     ] )->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'git clone {{ $repo }} {{ $directory }}' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )
                     ->times( 4 )
                     ->andReturnSelf();

        $command = $this->app->make( Create::class );

        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( Bootstrap::class, [], OutputStyle::class );

        $tester = $this->runCommand( $command, [
            '--branch' => 'main',
        ], [
            'What is the name of your project?'                                                                                     => 'test-project',
            'Enter the new github repo, e.g. https://github.com/moderntribe/$project-name. Leave blank to keep the existing remote' => 'https://github.com/moderntribe/test-project',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_removes_default_git_remotes(): void {
        $this->projectCreator->shouldReceive( 'setProjectId' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateNginxConf' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateDockerCompose' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateWpCli' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateGitWorkflows' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateCodeceptionConfig' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->projectCreator->shouldReceive( 'updateTestDumpSql' )
                             ->once()
                             ->with( 'test-project' )
                             ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->once()->with( [
            'directory' => 'test-project',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'with' )->once()->with( [
            'directory' => 'test-project',
        ] )->andReturnSelf();

        $this->runner->shouldReceive( 'enableTty' )
                     ->times( 3 )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'git -C {{ $directory }} branch -m develop' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'git -C {{ $directory }} remote rm origin' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'with' )
                     ->once()
                     ->with( [
                         'repo'      => 'https://github.com/moderntribe/square-one',
                         'directory' => 'test-project',
                     ] )->andReturnSelf();

        $this->runner->shouldReceive( 'run' )
                     ->once()
                     ->with( 'git clone {{ $repo }} {{ $directory }}' )
                     ->andReturnSelf();

        $this->runner->shouldReceive( 'throw' )
                     ->times( 3 )
                     ->andReturnSelf();

        $command = $this->app->make( Create::class );

        Artisan::shouldReceive( 'call' )
               ->once()
               ->with( Bootstrap::class, [], OutputStyle::class );

        $tester = $this->runCommand( $command, [
            'directory' => 'test-project',
        ], [
            'Enter the new github repo, e.g. https://github.com/moderntribe/$project-name. Leave blank to keep the existing remote' => '',
        ] );

        $this->assertSame( 0, $tester->getStatusCode() );
    }

    public function test_it_throws_exception_on_invalid_directory_name(): void {
        $this->expectException( SystemExitException::class );

        $command = $this->app->make( Create::class );

        $tester = $this->runCommand( $command, [], [
            'What is the name of your project?' => 'longer than the maximum allowed character limit for a directory or project name',
        ] );

        $this->assertSame( 1, $tester->getStatusCode() );
    }

    public function test_it_throws_exception_on_empty_project_name(): void {
        $this->expectException( SystemExitException::class );

        $command = $this->app->make( Create::class );

        $tester = $this->runCommand( $command, [], [
            'What is the name of your project?' => null,
        ] );

        $this->assertSame( 1, $tester->getStatusCode() );
        $this->assertStringContainsString( 'You must provide a project name', $tester->getDisplay() );
    }

}
