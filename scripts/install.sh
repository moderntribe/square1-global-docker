#!/usr/bin/env bash

#############################################################
# SquareOne Global Docker Installer
#############################################################

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );


# Functions
create_config_folder() {
  mkdir -p ~/.config/sq1
}

install_homebrew() {
  bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"
}

enable_bash_autocomplete() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sudo curl 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/3cc1406eccb2a694f13bf87cee64870ce44f81bb/sq1.autocompletion' -o $(brew --prefix)/etc/bash_completion.d/sq1.autocompletion
  else
    sudo curl 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/3cc1406eccb2a694f13bf87cee64870ce44f81bb/sq1.autocompletion' -o /etc/bash_completion.d/sq1.autocompletion
  fi
}

install_phar() {
  sudo curl 'https://github.com/moderntribe/square1-global-docker/releases/download/1.0.0-beta/sq1.phar' -o /usr/local/bin/sq1
  sudo chmod +x /usr/local/bin/sq1
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
