# Development / Contributing

Hey there! It's great you want to help out! This tool is built on [Laravel Zero](https://laravel-zero.com/docs/introduction/) so 
it would be best to start with their documentation.

### Prerequisites

In order to contribute to this project you must have the following installed:

- php version ^7.3 (7.4 recommended)
- The php-intl extension enabled with `extension=intl.so`
- `phar.readonly=0` set in your php.ini
- [PCOV](https://github.com/krakjoe/pcov): for calculating test coverage.

### Development installation

- If you have a pre `so` clone of [SquareOne](https://github.com/moderntribe/square-one), copy your existing SSL 
certificates from `dev/docker/global/certs` folder to the `~/.config/squareone/global/certs` folder.
- Stop all your existing containers `docker stop $(docker ps -aq)`.
- `git clone https://github.com/moderntribe/square1-global-docker`.
- Run `composer install` in the cloned folder.
- Back up your `~/mysql_data` folder in the event of any data loss.
- Back up your `~/.config/squareone` folder.
- Run [dev-install.sh](../install/dev-install.sh).
- type `sodev` in your terminal.

### Additional development commands

When not packaged as a phar, there are some additional commands available to you to aide in development:

| Command                    | Description                                                          |
|----------------------------|----------------------------------------------------------------------|
| sodev app:build            | Ignore this and use `composer build` to build a phar.                |
| sodev app:create-migration | Creates a migration class that you can then customize.               |
| sodev app:install          | Install Laravel Zero [add-ons](https://laravel-zero.com/docs/database). |
| sodev app:rename           | Renames the application. You shouldn't use this.                     |
| sodev app:test             | Runs automated tests.                                                |

### Automated tests

- In the project folder, run `./so app:test` to run all Feature and Unit tests.
- Run `composer coverage-html` to create the `coverage` folder in the root of the project that can be opened in a browser,
so you can see which code is missing tests.
- Run `composer badge` to generate a new svg badge to display code coverage % in the main README.md when your feature is complete.

### Releasing a new version

- Update the version in the [app.php](../config/app.php) file.
- Run `composer build` to package a phar to the [builds](../builds) directory.
- Create a new release/tag on GitHub and attach the generated `./builds/so.phar` in the binaries box in the 
[GitHub UI](https://docs.github.com/assets/images/help/releases/releases_adding_binary.gif).

### Configuration

All configuration for this project is stored in `~/.config/squareone`. 

#### Overriding Configuration  

There are a few options to override the configuration options.

1. Run `so config:copy` which will save the latest config file to `~/.config/squareone/squareone.yml`
1. Placing a `squareone.yml` file in the root of your local SquareOne project will override all other configuration options.

### Customize Global Docker

Run `so config:copy-compose` to save the latest `docker-compose.yml` to  `~/.config/squareone/docker-compose.yml` for customization.
