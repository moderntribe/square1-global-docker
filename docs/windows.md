# Windows Installation

## Requirements

1. Windows 10+.
2. Windows Subsystem 2.
3. Docker Desktop.

## Installation Instructions

1. Install [Windows Subsystem for Linux 2 (WSL)](https://docs.microsoft.com/en-us/windows/wsl/about). You may also use this [open source Powershell script](https://github.com/Layer8Err/WSL2setup), select either Ubuntu 20.04 or Debian.
2. Install [Docker Desktop for Windows](https://hub.docker.com/editions/community/docker-ce-desktop-windows).
3. Run the [installation command](../README.md#installation) inside your WSL VM.
4. Mount your Linux disk in Windows.

### TODO: Manually install SSL certificates

### TODO: Configure DNS

Set your Windows host DNS to use `127.0.0.1` and `1.1.1.1` or any valid DNS provider, as long as `127.0.0.1` is first.
