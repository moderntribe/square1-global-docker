<?php declare(strict_types=1);

namespace Tests\Feature\Services\CustomCommands;

use Illuminate\Support\Facades\Storage;
use NunoMaduro\LaravelConsoleSummary\SummaryCommand;
use Tests\Feature\Commands\BaseCommandTester;

final class CommandLoaderTest extends BaseCommandTester {

    /**
     * Assert the custom command added to the tests squareone.yml
     * config shows up in the summary/list command.
     *
     * @see config/tests/squareone.yml
     */
    public function test_custom_listdir_command_is_available() {
        $summaryCommand = $this->app->make( SummaryCommand::class );

        $tester = $this->runCommand( $summaryCommand );
        $output = $tester->getDisplay();

        $this->assertStringContainsString( 'project:listdir', $output );
        $this->assertStringContainsString( 'List directory contents', $output );
    }

    public function test_it_calls_custom_listdir_command() {
        Storage::disk( 'local' )->makeDirectory( 'tests/test-project/dev/docker' );

        copy( config_path( 'tests/squareone.yml' ), storage_path( 'tests/test-project/squareone.yml' ) );
        file_put_contents( storage_path( 'tests/test-project/dev/docker/.projectID' ), 'test' );

        $dir = getcwd();

        chdir( storage_path( 'tests/test-project' ) );

        $this->artisan( 'project:listdir', [
            'file' => 'command.txt',
            '--color' => 'yes',
        ] );

        $contents = file_get_contents( storage_path( 'tests/test-project/command.txt' ) );

        $this->assertStringContainsString( 'dev', $contents );
        $this->assertStringContainsString( 'squareone.yml', $contents );

        chdir( $dir );
    }

}
