# SquareOne Global Docker

Status: **Beta**

### Introduction

SquareOne Global docker is a command line application that powers management of projects for [SquareOne](https://github.com/moderntribe/square-one).

### Requirements

1. PHP 7.2+
1. curl
1. composer
1. docker
1. docker-compose
1. docker-credential-helper (osx)
1. bash-completion (if you want "so" autocomplete commands)
1. git

### Installation

Note for macOS users: This script will install brew and all of the requirements listed above.

`bash -c "$(curl -fsSL https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/scripts/install.sh)"`

### Usage

- Run `so` to see a command list.
- To pass arguments to a command, separate the arguments with `--`, e.g. `so wp -- cli info --format=json`

```
SquareOne Global Docker 2.2.1-beta

Usage:
  command [options] [arguments]

Options:
  -h, --help                           Display this help message
  -q, --quiet                          Do not output any message
  -V, --version                        Display this application version
      --ansi                           Force ANSI output
      --no-ansi                        Disable ANSI output
  -n, --no-interaction                 Do not ask any interactive question
      --simulate                       Run in simulated mode (show what would have happened).
      --progress-delay=PROGRESS-DELAY  Number of seconds before progress bar is displayed in long-running task collections. Default: 2s. [default: 2]
  -D, --define=DEFINE                  Define a configuration item value. (multiple values allowed)
  -p, --project-path=PROJECT-PATH      Path to a SquareOne project [default: {"name":"project-path","shortcut":"p","description":"Path to a SquareOne project"}]
  -v|vv|vvv, --verbose                 Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  composer             Runs a composer command in the local docker container
  gulp                 Run a Gulp command
  help                 Displays help for a command
  list                 Lists commands
  logs                 Displays local SquareOne project docker logs
  migrate-domain       Migrate a recently imported remote database to your local
  restart              Restarts your local SquareOne project
  shell                Gives you a shell into the php-fpm docker container
  start                Starts your local SquareOne project, run anywhere in a project folder
  stop                 Stops your local SquareOne project, run anywhere in a project folder
  test                 Run Codeception tests
  wp                   Run WP CLI commands in the SquareOne local container
 config
  config:compose-copy  Copies the Global docker-compose.yml file to the local config folder for customization
  config:copy          Copies the squareone.yml file to the local config folder for customization
 global
  global:cert          Generates an SSL certificate for a local .tribe domain
  global:logs          Displays SquareOne global docker logs
  global:myadmin       Start a phpMyAdmin docker container. Default: http://localhost:8080
  global:restart       Restarts the SquareOne global docker container
  global:start         Starts the SquareOne global docker container
  global:status        Shows all running docker containers
  global:stop          Stops the SquareOne global docker container
  global:stop-all      Stops ALL running docker containers on your system
 self
  self:update          [update] Updates SquareOne Global Docker to the latest version.
  self:update-check    Check if there is an updated phar to self update
```

### Configuration

All configuration for this project is stored in `~/.config/squareone`. 

#### Overriding Configuration  

There are a few options to override the configuration options.

1. Run `so config:copy` which will save the latest config file to `~/.config/squareone/squareone.yml`
1. Placing a `squareone.yml` file in the root of your local SquareOne project will override all other configuration options.

### Customize Global Docker

Run `so config:copy-compose` to save the latest `docker-compose.yml` to  `~/.config/squareone/docker-compose.yml` for customization.

### Development installation

1. Clone this repo.
1. If you already have an existing square-one repo you're using for your global, copy the `dev/docker/global/certs` folder to the `~/.config/squareone/global/certs` folder.
1. Stop all your existing containers `docker stop $(docker ps -aq)`.
1. Run `./scripts/dev-install.sh` which will create the `sodev` command.

### Releasing a new version

1. Update the version in the [VERSION](./VERSION) file.
1. Run `composer run-script phar:install-tools`
1. Run `composer run-script autocomplete:build`
1. Run `composer dump-autoload -o --no-dev`
1. Run `composer run-script phar:build`
1. Create a new release/tag on GitHub and attach the generated `so.phar` in the root directory in the binaries box.
1. Once the release is created you may want to bump the VERSION via semver with the `-dev` suffix.

### Credits

Brought to you by [Modern Tribe](https://tri.be/). Read [License](LICENSE.md) and [Contribution](CONTRIBUTING.md) docs.

Powered by [Robo](https://robo.li/)


