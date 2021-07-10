function __fish_so_no_subcommand
    for i in (commandline -opc)
        if contains -- $i bootstrap composer create docker-compose help list logs migrate-domain open restart share shell start stop test wp xdebug config:compose-copy config:copy global:cert global:logs global:myadmin global:portainer global:restart global:start global:status global:stop global:stop-all schedule:finish schedule:run self:update self:update-check vendor:publish
            return 1
        end
    end
    return 0
end

# global options
complete -c so -n '__fish_so_no_subcommand' -l help -d 'Display help for the given command. When no command is given display help for the list command'
complete -c so -n '__fish_so_no_subcommand' -l quiet -d 'Do not output any message'
complete -c so -n '__fish_so_no_subcommand' -l verbose -d 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'
complete -c so -n '__fish_so_no_subcommand' -l version -d 'Display this application version'
complete -c so -n '__fish_so_no_subcommand' -l ansi -d 'Force (or disable --no-ansi) ANSI output'
complete -c so -n '__fish_so_no_subcommand' -l no-ansi -d 'Negate the "--ansi" option'
complete -c so -n '__fish_so_no_subcommand' -l no-interaction -d 'Do not ask any interactive question'
complete -c so -n '__fish_so_no_subcommand' -l env -d 'The environment the command should run under'

# commands
complete -c so -f -n '__fish_so_no_subcommand' -a bootstrap -d 'Bootstrap WordPress: Install core, create an admin user'
complete -c so -f -n '__fish_so_no_subcommand' -a composer -d 'Run a composer command in the local docker container'
complete -c so -f -n '__fish_so_no_subcommand' -a create -d 'Create a new SquareOne project based off of the square-one framework'
complete -c so -f -n '__fish_so_no_subcommand' -a docker-compose -d 'Pass through for docker-compose binary'
complete -c so -f -n '__fish_so_no_subcommand' -a help -d 'Display help for a command'
complete -c so -f -n '__fish_so_no_subcommand' -a list -d 'List commands'
complete -c so -f -n '__fish_so_no_subcommand' -a logs -d 'Displays local SquareOne project docker logs'
complete -c so -f -n '__fish_so_no_subcommand' -a migrate-domain -d 'Migrate a recently imported remote database to your local; Automatically detects the domain name.'
complete -c so -f -n '__fish_so_no_subcommand' -a open -d 'Open\'s a URL in your default browser or the current SquareOne project'
complete -c so -f -n '__fish_so_no_subcommand' -a restart -d 'Restarts your local SquareOne project'
complete -c so -f -n '__fish_so_no_subcommand' -a share -d 'Share your local project on a temporary URL using ngrok'
complete -c so -f -n '__fish_so_no_subcommand' -a shell -d 'Gives you a bash shell into the php-fpm docker container'
complete -c so -f -n '__fish_so_no_subcommand' -a start -d 'Starts your local SquareOne project, run anywhere in a project folder'
complete -c so -f -n '__fish_so_no_subcommand' -a stop -d 'Stops your local SquareOne project, run anywhere in a project folder'
complete -c so -f -n '__fish_so_no_subcommand' -a test -d 'Run codeception tests in the SquareOne local container'
complete -c so -f -n '__fish_so_no_subcommand' -a wp -d 'Run WP CLI commands in the SquareOne local container'
complete -c so -f -n '__fish_so_no_subcommand' -a xdebug -d 'Enable/disable Xdebug in the php-fpm container to increase performance on MacOS'
complete -c so -f -n '__fish_so_no_subcommand' -a config:compose-copy -d 'Copies the Global docker-compose.yml file to the local config folder for customization'
complete -c so -f -n '__fish_so_no_subcommand' -a config:copy -d 'Copies the squareone.yml file to the local config folder for customization'
complete -c so -f -n '__fish_so_no_subcommand' -a global:cert -d 'Manually generate a certificate for a local domain'
complete -c so -f -n '__fish_so_no_subcommand' -a global:logs -d 'Displays SquareOne global docker logs'
complete -c so -f -n '__fish_so_no_subcommand' -a global:myadmin -d 'Starts a phpMyAdmin container'
complete -c so -f -n '__fish_so_no_subcommand' -a global:portainer -d 'Launches Portainer docker management'
complete -c so -f -n '__fish_so_no_subcommand' -a global:restart -d 'Restarts the SquareOne global docker containers'
complete -c so -f -n '__fish_so_no_subcommand' -a global:start -d 'Starts the SquareOne global docker containers'
complete -c so -f -n '__fish_so_no_subcommand' -a global:status -d 'Shows all running docker containers'
complete -c so -f -n '__fish_so_no_subcommand' -a global:stop -d 'Stops the SquareOne global docker containers'
complete -c so -f -n '__fish_so_no_subcommand' -a global:stop-all -d 'Stops all running docker containers'
complete -c so -f -n '__fish_so_no_subcommand' -a schedule:finish -d 'Handle the completion of a scheduled command'
complete -c so -f -n '__fish_so_no_subcommand' -a schedule:run -d 'Run the scheduled commands'
complete -c so -f -n '__fish_so_no_subcommand' -a self:update -d 'Updates the application, if available'
complete -c so -f -n '__fish_so_no_subcommand' -a self:update-check -d 'Check if there is an updated release'
complete -c so -f -n '__fish_so_no_subcommand' -a vendor:publish -d 'Publish any publishable assets from vendor packages'

# command options

# bootstrap

# composer

# create
complete -c so -A -n '__fish_seen_subcommand_from create' -l remote -d 'Sets a new git remote, e.g. https://github.com/moderntribe/$project/'
complete -c so -A -n '__fish_seen_subcommand_from create' -l no-bootstrap -d 'Do not attempt to automatically configure the project'

# docker-compose

# help
complete -c so -A -n '__fish_seen_subcommand_from help' -l format -d 'The output format (txt, xml, json, or md)'
complete -c so -A -n '__fish_seen_subcommand_from help' -l raw -d 'To output raw command help'

# list
complete -c so -A -n '__fish_seen_subcommand_from list' -l raw -d 'To output raw command list'
complete -c so -A -n '__fish_seen_subcommand_from list' -l format -d 'The output format (txt, xml, json, or md)'
complete -c so -A -n '__fish_seen_subcommand_from list' -l short -d 'To skip describing commands\' arguments'

# logs

# migrate-domain

# open

# restart

# share

# shell
complete -c so -A -n '__fish_seen_subcommand_from shell' -l user -d 'The username or UID of the account to use'

# start
complete -c so -A -n '__fish_seen_subcommand_from start' -l browser -d 'Automatically open the project in your browser'
complete -c so -A -n '__fish_seen_subcommand_from start' -l path -d 'Path to a specific local project folder'
complete -c so -A -n '__fish_seen_subcommand_from start' -l remove-orphans -d 'Remove containers for services not in the compose file'
complete -c so -A -n '__fish_seen_subcommand_from start' -l skip-global -d 'Skip starting global containers'

# stop
complete -c so -A -n '__fish_seen_subcommand_from stop' -l remove-orphans -d 'Remove containers for services not in the compose file'

# test
complete -c so -A -n '__fish_seen_subcommand_from test' -l xdebug -d 'Enable xdebug'
complete -c so -A -n '__fish_seen_subcommand_from test' -l container -d 'Set the docker container to run the tests on'
complete -c so -A -n '__fish_seen_subcommand_from test' -l noclean -d 'Do not run the codecept clean command first'
complete -c so -A -n '__fish_seen_subcommand_from test' -l notty -d 'Disable interactive/tty to capture output'

# wp
complete -c so -A -n '__fish_seen_subcommand_from wp' -l xdebug -d 'Enable xdebug'
complete -c so -A -n '__fish_seen_subcommand_from wp' -l notty -d 'Disable interactive/tty to capture output'

# xdebug

# config:compose-copy

# config:copy

# global:cert
complete -c so -A -n '__fish_seen_subcommand_from global:cert' -l wildcard -d 'Allow *.tribe wildcard generation'

# global:logs

# global:myadmin

# global:portainer

# global:restart

# global:start

# global:status

# global:stop

# global:stop-all

# schedule:finish

# schedule:run

# self:update

# self:update-check
complete -c so -A -n '__fish_seen_subcommand_from self:update-check' -l force -d 'Force an uncached check'
complete -c so -A -n '__fish_seen_subcommand_from self:update-check' -l only-new -d 'Only show a notice if an update is available'

# vendor:publish
complete -c so -A -n '__fish_seen_subcommand_from vendor:publish' -l force -d 'Overwrite any existing files'
complete -c so -A -n '__fish_seen_subcommand_from vendor:publish' -l all -d 'Publish assets for all service providers without prompt'
complete -c so -A -n '__fish_seen_subcommand_from vendor:publish' -l provider -d 'The service provider that has assets you want to publish'
complete -c so -A -n '__fish_seen_subcommand_from vendor:publish' -l tag -d 'One or many tags that have assets you want to publish'
