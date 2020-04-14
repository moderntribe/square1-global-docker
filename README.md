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
1. bash-completion (if you want sq1 autocomplete commands)
1. git

### Installation

Note for macOS users: This script will install brew and all of the requirements listed above.

`bash -c "$(curl -fsSL https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/scripts/install.sh)"`

### Usage

- Run `sq1` to see a command list.
- To pass arguments to a command, separate the arguments with `--`, e.g. `sq1 wp cli info -- --format=json`

```
SquareOne Global Docker 1.1.0-beta

Usage:
  command [options] [arguments]

Options:
  -h, --help                       Display this help message
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi                       Force ANSI output
      --no-ansi                    Disable ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -p, --project-path=PROJECT-PATH  Path to a SquareOne project [default: {"name":"project-path","shortcut":"p","description":"Path to a SquareOne project"}]
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Available commands:
  composer             Runs a composer command in the local docker container
  gulp                 Run a Gulp command
  help                 Displays help for a command
  list                 Lists commands
  logs                 Displays local SquareOne project docker logs
  restart              Restarts your local SquareOne project
  shell                Gives you a shell into the php-fpm docker container
  start                Starts your local SquareOne project, run anywhere in a project folder
  stop                 Stops your local SquareOne project, run anywhere in a project folder
  test                 Run Codeception tests
  wp                   Run WP CLI commands in the SquareOne local container
 config
  config:compose-copy  Copies the Global docker-compose.yml file to the local config folder for customization
  config:copy          Copies the sq1.yml file to the local config folder for customization
 global
  global:cert          Generates an SSL certificate for a local .tribe domain
  global:logs          Displays SquareOne global docker logs
  global:myadmin       Start a phpMyAdmin docker container. Default: http://localhost:8080
  global:restart       Restarts the SquareOne global docker container
  global:start         Starts the SquareOne global docker container
  global:status        Shows all running docker containers
  global:stop          Stops the SquareOne global docker container
  global:stop-all      Stops ALL running docker containers on your system

```

### Configuration

All configuration for this project is stored in `~/.config/sq1`. 

#### Overriding Configuration  

There are a few options to override the configuration options.

1. Run `sq1 config:copy` which will save the latest config file to `~/.config/sq1/sq1.yml`
1. Placing a `sq1.yml` file in the root of your local SquareOne project will override all other configuration options.

### Customize Global Docker

Run `sq1 config:copy-compose` to save the latest `docker-compose.yml` to  `~/.config/sq1/docker-compose.yml` for customization.

### Development installation

1. Clone this repo.
1. If you already have an existing square-one repo you're using for your global, copy the `dev/docker/global/certs` folder to the `~/.config/sq1/global/certs` folder.
1. Stop all your existing containers `docker stop $(docker ps -aq)`.
1. Run `./scripts/dev-install.sh`

### Releasing a new version

1. Update the version in the [VERSION](./VERSION) file.
1. Run `composer run-script phar:install-tools`
1. Run `composer run-script phar:build`
1. Run `composer run-script autocomplete:build`
1. Create a new release/tag on GitHub and attach the generated `sq1.phar` in the root directory in the binaries box.
1. Once the release is created you may want to bump the VERSION via semver with the `-dev` suffix.


