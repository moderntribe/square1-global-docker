#compdef so

_so()
{
    local state com cur

    cur=${words[${#words[@]}]}

    # lookup for command
    for word in ${words[@]:1}; do
        if [[ $word != -* ]]; then
            com=$word
            break
        fi
    done

    if [[ ${cur} == --* ]]; then
        state="option"
        opts=("--help:Display help for the given command. When no command is given display help for the <info>list</info> command" "--quiet:Do not output any message" "--verbose:Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug" "--version:Display this application version" "--ansi:Force \(or disable --no-ansi\) ANSI output" "--no-ansi:Negate the "--ansi" option" "--no-interaction:Do not ask any interactive question" "--env:The environment the command should run under")
    elif [[ $cur == $com ]]; then
        state="command"
        coms=("_complete:Internal command to provide shell completion suggestions" "bootstrap:Bootstrap WordPress: Install core, create an admin user" "completion:Dump the shell completion script" "composer:Run a composer command in the local docker container" "create:Create a new SquareOne project based off of the square-one framework" "docker:Pass through for the docker binary" "docker-compose:Pass through for docker-compose binary" "help:Display help for a command" "list:List commands" "logs:Displays local SquareOne project docker logs" "migrate-domain:Migrate a recently imported remote database to your local\; Automatically detects the domain name." "open:Open\'s a URL in your default browser or the current SquareOne project" "restart:Restarts your local SquareOne project" "share:Share your local project on a temporary URL using ngrok" "shell:Gives you a bash shell into the php-fpm docker container" "start:Starts your local SquareOne project, run anywhere in a project folder" "stop:Stops your local SquareOne project, run anywhere in a project folder" "test:Run codeception tests in the SquareOne php container" "wp:Run WP CLI commands in the SquareOne local container" "xdebug:Enable/disable Xdebug in the php-fpm container to increase performance on MacOS" "config\:compose-copy:Copies the Global docker-compose.yml file to the local config folder for customization" "config\:copy:Copies the squareone.yml file to the local config folder for customization" "global\:cert:Manually generate a certificate for a local domain" "global\:logs:Displays SquareOne global docker logs" "global\:myadmin:Starts a phpMyAdmin container" "global\:portainer:Launches Portainer docker management" "global\:restart:Restarts the SquareOne global docker containers" "global\:start:Starts the SquareOne global docker containers" "global\:status:Shows all running docker containers" "global\:stop:Stops the SquareOne global docker containers" "global\:stop-all:Stops all running docker containers" "schedule\:finish:Handle the completion of a scheduled command" "schedule\:run:Run the scheduled commands" "self\:update:Updates the application, if available" "self\:update-check:Check if there is an updated release" "vendor\:publish:Publish any publishable assets from vendor packages")
    fi

    case $state in
        command)
            _describe 'command' coms
        ;;
        option)
            case "$com" in

            _complete)
            opts+=("--shell:The shell type \("bash"\)" "--input:An array of input tokens \(e.g. COMP_WORDS or argv\)" "--current:The index of the "input" array that the cursor is in \(e.g. COMP_CWORD\)" "--symfony:The version of the completion script")
            ;;

            bootstrap)
            opts+=("--multisite:Bootstrap for a multisite project")
            ;;

            completion)
            opts+=("--debug:Tail the completion debug log")
            ;;

            composer)
            opts+=()
            ;;

            create)
            opts+=("--remote:Sets a new git remote, e.g. https://github.com/moderntribe/\$project/" "--no-bootstrap:Do not attempt to automatically configure the project" "--branch:Create the project by using a specific branch/commit from github.com/moderntribe/square-one")
            ;;

            docker)
            opts+=()
            ;;

            docker-compose)
            opts+=()
            ;;

            help)
            opts+=("--format:The output format \(txt, xml, json, or md\)" "--raw:To output raw command help")
            ;;

            list)
            opts+=("--raw:To output raw command list" "--format:The output format \(txt, xml, json, or md\)" "--short:To skip describing commands\' arguments")
            ;;

            logs)
            opts+=()
            ;;

            migrate-domain)
            opts+=()
            ;;

            open)
            opts+=()
            ;;

            restart)
            opts+=()
            ;;

            share)
            opts+=("--content-dir:The name of the wp-content directory, if renamed" "--not-wordpress:Attempt to share a non-WordPress project")
            ;;

            shell)
            opts+=("--user:The user, uid or "user:group" to enter the shell as")
            ;;

            start)
            opts+=("--browser:Automatically open the project in your browser" "--path:Path to a specific local project folder" "--remove-orphans:Remove containers for services not in the compose file" "--skip-global:Skip starting global containers")
            ;;

            stop)
            opts+=("--remove-orphans:Remove containers for services not in the compose file")
            ;;

            test)
            opts+=("--xdebug:Enable xdebug" "--container:Set the docker container to run the tests on" "--path:The path to the tests directory in the container" "--noclean:Do not run the codecept clean command first" "--notty:Disable interactive/tty to capture output")
            ;;

            wp)
            opts+=("--xdebug:Enable xdebug" "--notty:Disable interactive/tty to capture output")
            ;;

            xdebug)
            opts+=()
            ;;

            config:compose-copy)
            opts+=()
            ;;

            config:copy)
            opts+=()
            ;;

            global:cert)
            opts+=("--wildcard:Allow \*.tribe wildcard generation")
            ;;

            global:logs)
            opts+=()
            ;;

            global:myadmin)
            opts+=()
            ;;

            global:portainer)
            opts+=()
            ;;

            global:restart)
            opts+=()
            ;;

            global:start)
            opts+=()
            ;;

            global:status)
            opts+=()
            ;;

            global:stop)
            opts+=()
            ;;

            global:stop-all)
            opts+=()
            ;;

            schedule:finish)
            opts+=()
            ;;

            schedule:run)
            opts+=()
            ;;

            self:update)
            opts+=()
            ;;

            self:update-check)
            opts+=("--force:Force an uncached check" "--only-new:Only show a notice if an update is available")
            ;;

            vendor:publish)
            opts+=("--force:Overwrite any existing files" "--all:Publish assets for all service providers without prompt" "--provider:The service provider that has assets you want to publish" "--tag:One or many tags that have assets you want to publish")
            ;;

            esac

            _describe 'option' opts
        ;;
        *)
            # fallback to file completion
            _arguments '*:file:_files'
    esac
}

compdef _so so
