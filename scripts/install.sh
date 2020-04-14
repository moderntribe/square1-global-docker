#!/usr/bin/env bash

#############################################################
# SquareOne Global Docker Installer
#############################################################

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
PHAR_NAME="sq1.phar"
CONFIG_DIR=~/.config/sq1

# Functions
create_config_folder() {
  if [ ! -d "${CONFIG_DIR}" ]; then
    mkdir -p ${CONFIG_DIR}
  fi
}

install_homebrew() {
  bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"
}

enable_bash_autocomplete() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sudo curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/sq1.autocompletion' -o $(brew --prefix)/etc/bash_completion.d/sq1.autocompletion
  else
    sudo curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/sq1.autocompletion' -o /etc/bash_completion.d/sq1.autocompletion
  fi
}

install_phar() {
  PHAR_DOWNLOAD=$(curl -s https://api.github.com/repos/moderntribe/square1-global-docker/releases/latest \
        | grep browser_download_url \
        | grep ${PHAR_NAME} \
        | cut -d '"' -f 4)

  curl -s -L --create-dirs "${PHAR_DOWNLOAD}" -o ${CONFIG_DIR}/bin/sq1
  chmod +x ${CONFIG_DIR}/bin/sq1
  sudo ln -s ${CONFIG_DIR}/bin/sq1 /usr/local/bin/sq1
}

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
  else
      brew update
  fi

  echo "Installing dependencies via brew..."
  brew install $(<brew/packages.txt)
fi

echo "Creating config folder: ~/.config/sq1"
create_config_folder

echo "Enabling sq1 autocompletion, enter your password when requested."
enable_bash_autocomplete

echo "Downloading sq1.phar to /usr/local/bin/sq1, enter your password when requested."
install_phar

sq1

echo ""
echo "************************************"
echo "If everything went smoothly, you should see the sq1 command list above. Reload your terminal to enable autocompletion."
echo "************************************"
