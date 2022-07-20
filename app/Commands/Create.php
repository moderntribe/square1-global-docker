<?php declare(strict_types=1);

namespace App\Commands;

use App\Contracts\Runner;
use Illuminate\Support\Str;
use App\Services\ProjectCreator;
use App\Commands\LocalDocker\Bootstrap;
use App\Exceptions\SystemExitException;
use Illuminate\Support\Facades\Artisan;

/**
 * Class Create
 *
 * @package App\Commands
 */
class Create extends BaseCommand {

    public const DIRECTORY_LIMIT = 32;
    public const REPO            = 'https://github.com/moderntribe/square-one';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'create {directory?     : Directory and project name where the files should be created}
                                   {--remote=      : Sets a new git remote, e.g. https://github.com/moderntribe/$project/}
                                   {--no-bootstrap : Do not attempt to automatically configure the project}
                                   {--branch=      : Create the project by using a specific branch/commit from github.com/moderntribe/square-one}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new SquareOne project based off of the square-one framework';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\ProjectCreator  $creator  The project creator.
     *
     * @param  \App\Contracts\Runner         $runner   The command runner.
     *
     * @return void
     *
     * @throws \App\Exceptions\SystemExitException
     */
    public function handle( ProjectCreator $creator, Runner $runner ): void {
        $directory = $this->argument( 'directory' );

        if ( empty( $directory ) ) {
            $directory = $this->ask( 'What is the name of your project?' );

            if ( empty( $directory ) ) {
                throw new SystemExitException( 'You must provide a project name' );
            }
        }

        $directory = Str::slug( $directory );

        if ( Str::length( $directory ) > self::DIRECTORY_LIMIT ) {
            throw new SystemExitException( sprintf( 'The directory/project name cannot be longer than %d characters!', self::DIRECTORY_LIMIT ) );
        }

        $this->task( sprintf( '<comment>Cloning %s into %s</comment>', self::REPO, $directory ),
            call_user_func( [ $this, 'cloneRepo' ], $runner, $directory ) );

        $branch = $this->option( 'branch' );

        if ( $branch ) {
            $this->task( sprintf( '<comment>Checking out %s</comment>', $branch ),
                call_user_func( [ $this, 'checkoutBranch' ], $runner, $directory, $branch ) );
        }

        $this->task( '<comment>Setting default branch to develop</comment>',
            call_user_func( [ $this, 'renameDefaultBranch' ], $runner, $directory ) );

        $remote = $this->option( 'remote' );

        if ( empty( $remote ) ) {
            $remote = $this->ask( sprintf(
                'Enter the new github repo, e.g. https://github.com/moderntribe/%s. Leave blank to remove all remotes.',
                $directory
            ) );

            if ( ! empty( $remote ) ) {
                $this->task( sprintf( '<comment>Setting new remote origin to: %s</comment>', $remote ),
                    call_user_func( [ $this, 'setGitRemote' ], $runner, $directory, $remote ) );
            } else {
                $this->task( '<comment>Removing default remotes</comment>',
                    call_user_func( [ $this, 'removeGitRemotes' ], $runner, $directory ) );
            }
        }

        $this->task( sprintf( '<comment>Configuring %s</comment>', $directory ), call_user_func( [ $this, 'configureProject' ], $directory, $creator ) );

        if ( ! $this->option( 'no-bootstrap' ) ) {
            chdir( $directory );
            Artisan::call( Bootstrap::class, [], $this->output );
        }
    }

    /**
     * Clone the square-one repo.
     *
     * @param  \App\Contracts\Runner  $runner     The command runner.
     * @param  string                 $directory  The directory to clone the project to.
     */
    public function cloneRepo( Runner $runner, string $directory ): void {
        $runner->with( [
            'repo'      => self::REPO,
            'directory' => $directory,
        ] )->enableTty()
               ->run( 'git clone {{ $repo }} {{ $directory }}' )
               ->throw();
    }

    /**
     * Checks out a specific branch or commit.
     *
     * @param  \App\Contracts\Runner  $runner     The command runner.
     * @param  string                 $directory  The directory the project was cloned to.
     * @param  string                 $branch     The branch or commit to checkout.
     */
    public function checkoutBranch( Runner $runner, string $directory, string $branch ): void {
        $runner->with( [
            'directory' => $directory,
            'branch'    => $branch,
        ] )->enableTty()
               ->run( 'git -C {{ $directory }} checkout {{ $branch }}' )
               ->throw();
    }

    /**
     * Renames the default branch.
     *
     * @param  \App\Contracts\Runner  $runner     The command runner.
     * @param  string                 $directory  The directory the project was cloned to.
     */
    public function renameDefaultBranch( Runner $runner, string $directory ): void {
        $runner->with( [
            'directory' => $directory,
        ] )->enableTty()
               ->run( 'git -C {{ $directory }} branch -m develop' )
               ->throw();
    }

    /**
     * Set the git remote URL.
     *
     * @param  \App\Contracts\Runner  $runner     The command runner.
     * @param  string                 $directory  The directory to clone the project to.
     * @param  string                 $remote     The git remote to set.
     */
    public function setGitRemote( Runner $runner, string $directory, string $remote ): void {
        $runner->with( [
            'directory' => $directory,
            'remote'    => $remote,
        ] )->enableTty()
               ->run( 'git -C {{ $directory }} remote set-url origin {{ $remote }}' )
               ->throw();
    }

    /**
     * Removes the default SquareOne remotes.
     *
     * @param  \App\Contracts\Runner  $runner     The command runner.
     * @param  string                 $directory  The directory to clone the project to.
     */
    public function removeGitRemotes( Runner $runner, string $directory ): void {
        $runner->with( [
            'directory' => $directory,
        ] )->enableTty()
               ->run( 'git -C {{ $directory }} remote rm origin' )
               ->throw();
    }

    /**
     * Automatically configure the project.
     *
     * @param  string                        $directory  The project's directory.
     * @param  \App\Services\ProjectCreator  $creator    The project creator.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function configureProject( string $directory, ProjectCreator $creator ): void {
        $creator->setProjectId( $directory )
                ->updateNginxConf( $directory )
                ->updateDockerCompose( $directory )
                ->updateWpCli( $directory )
                ->updateGitWorkflows( $directory )
                ->updateCodeceptionConfig( $directory )
                ->updateTestDumpSql( $directory );
    }

}
