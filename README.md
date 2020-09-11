# SquareOne Docker

> SquareOne Docker is a command line application that provides a local docker based development environment for projects 
> based on the [SquareOne](https://github.com/moderntribe/square-one) framework. This is an internal Modern Tribe project, 
> while you may use it, it's heavily based on our internal tools and workflow and many features will not work out of the box.

Status: **Stable**

![CI](https://github.com/moderntribe/square1-global-docker/workflows/CI/badge.svg) [![Coverage](badges/coverage.svg)](https://github.com/moderntribe/square1-global-docker/actions?query=workflow%3ACI)

![so list](./docs/img/so.svg)

### Requirements

1. PHP 7.2.5+ (with php-xml, php-zlib)
1. curl
1. composer
1. docker
1. docker-compose
1. docker-credential-helper (osx)
1. bash-completion (if you want "so" autocomplete commands)
1. git

### Prerequisites

You **must not** have any existing dns, web server or mysql/mariadb services running before installing. Stop any
MAMP/Valet/Docker services before running the installer.

The following ports should be available:

| Port | Service       |
|------|---------------|
| 80   | Nginx         |
| 443  | Nginx         |
| 53   | Dnsmasq       |
| 8080 | PhpMyAdmin    |
| 9090 | Portainer     |
| 3306 | MariaDB       |
| 4444 | Chrome driver |

### Operating System Support

- [x] MacOS
- [x] Linux (Debian, Arch, openSUSE or RedHat based distros)
- [ ] Windows ¯\_(ツ)_/¯

### Installation

Copy the following in your terminal:

`bash -c "$(curl -fsSL https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/install/install.sh)"`

<sup>Note for macOS users: This script will install brew and all the requirements listed above.</sup>
<sup>Note for Linux users: Ensure you have installed the required packages with your distribution's package manager first.</sup>

### Usage

- Run `so` to see a command list.
- To pass arguments to a sub command, separate the arguments with `--`, e.g. `so wp -- cli info --format=json`

### Development / Contributing

See [Developer Docs](./docs/dev.md)

### Credits

Brought to you by [Modern Tribe](https://tri.be/). Read [License](LICENSE.md) and [Contribution](CONTRIBUTING.md) docs.

Powered by [Laravel Zero](https://laravel-zero.com/)
