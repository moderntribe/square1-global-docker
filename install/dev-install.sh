#!/usr/bin/env bash

#############################################################
# SquareOne Global Docker Installer
#############################################################

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
CONFIG_DIR=~/.config/squareone

# Functions
create_config_folder() {
  if [ ! -d "${CONFIG_DIR}" ]; then
    mkdir -p ${CONFIG_DIR}
  fi
}

install_homebrew() {
  bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"
}

enable_autocomplete() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sudo curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion' -o $(brew --prefix)/etc/bash_completion.d/squareone.dev.autocompletion
    curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion.zsh' -o ~/.squareone_dev_completion.zsh && echo "source ~/.squareone_dev_completion.zsh" >> ~/.zshrc
  else
    sudo curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion' -o /etc/bash_completion.d/squareone.dev.autocompletion
    curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion.fish' -o ~/.config/fish/completions/so.dev.fish
  fi
}

symlink_sq1() {
  sudo ln -s ${SCRIPTDIR}/../so /usr/local/bin/sodev
}

install_nvm() {
    curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | bash
}

echo "Creating config folder: ~/.config/squareone"
create_config_folder

# OSX
if [[ "$OSTYPE" == "darwin"* ]]; then
  command -v docker >/dev/null 2>&1
  if [[ $? != 0 ]] ; then
      echo "Docker appears to be missing, install it from here: https://hub.docker.com/editions/community/docker-ce-desktop-mac"
      exit 1;
  fi

  command -v brew >/dev/null 2>&1
  if [[ $? != 0 ]] ; then
      echo "Homebrew not found, do you want to install it?"
      select yn in "Yes" "No"; do
          case $yn in
              Yes ) install_homebrew; break;;
              No ) exit;;
          esac
      done
  fi

  echo "Installing dependencies via brew..."
  curl -s https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/brew/packages.txt -o ${CONFIG_DIR}/packages.txt
  brew install "$(<${CONFIG_DIR}/packages.txt)"
  echo "Setting the default PHP version to 7.4..."
  brew link php@7.4 --force
fi

# Debian Linux flavors
if [[ -x "$(command -v apt-get)" ]]; then
    echo "* Installing dependencies via apt, enter your sudo password when requested..."

    echo "* Removing legacy docker installs..."
    sudo apt-get remove docker docker-engine docker.io containerd runc

    echo "Preparing docker sources..."
    sudo apt-get install -y \
        apt-transport-https \
        ca-certificates \
        curl \
        gnupg \
        lsb-release
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
    echo \
      "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
      $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

    echo "* Updating and upgrading apt..."
    sudo apt-get update -y && sudo apt-get upgrade -y
    curl -fsSL https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/install/debian/apt.txt -o ${CONFIG_DIR}/apt.txt

    echo "* Installing docker-compose..."
    sudo curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose

    echo "* Installing packages..."
    xargs -a ${CONFIG_DIR}/apt.txt sudo apt-get install -y

    echo "* Installing docker-compose bash completion"
    sudo curl \
        -L https://raw.githubusercontent.com/docker/compose/1.29.2/contrib/completion/bash/docker-compose \
        -o /etc/bash_completion.d/docker-compose

    echo "* Installing nameservers to /etc/resolv.conf.head..."
    sudo curl -fsSL https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/install/debian/resolv.conf.head -o /etc/resolv.conf.head

    echo "* Backing up /etc/NetworkManager/NetworkManager.conf and creating a version that uses openresolv..."
    sudo mv /etc/NetworkManager/NetworkManager.conf /etc/NetworkManager/NetworkManager.conf.bak
    sudo curl -s https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/install/debian/NetworkManager.conf -o /etc/NetworkManager/NetworkManager.conf

    echo "* Disabling systemd-resolved DNS service..."
    sudo systemctl disable systemd-resolved
    sudo systemctl stop systemd-resolved
    sudo rm -rf /etc/resolv.conf
    echo "* Generating a new /etc/resolv.conf..."
    sudo resolvconf -u

    echo "* Fixing docker permissions"
    sudo usermod -a -G docker "$USER"
fi

echo "Installing nvm"
install_nvm

echo "Enabling SquareOne autocompletion, enter your password when requested."
enable_autocomplete

echo "Running composer install..."
composer install -d ${SCRIPTDIR}/../

echo "Symlinking so binary to /usr/local/bin/so, enter your password when requested."
symlink_sq1

sodev

echo ""
echo "************************************"
echo "If everything went smoothly, you should see the 'sodev' command list above. Reboot to properly complete installation."
echo "************************************"
echo "* Reboot now to complete the installation [y/n]?"
read -r CHOICE
if [[ $CHOICE == y* ]]; then
    sudo reboot
else
    echo "* Done! Make sure you reboot to complete the installation."
fi
