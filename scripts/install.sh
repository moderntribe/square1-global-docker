#!/usr/bin/env bash

#############################################################
# SquareOne Docker Installer
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
    sudo cp -f sq1.autocompletion $(brew --prefix)/etc/bash_completion.d/sq1.autocompletion
  else
    sudo cp -f sq1.autocompletion /etc/bash_completion.d/sq1.autocompletion
  fi
}

symlink_sq1() {
  sudo ln -s ${SCRIPTDIR}/sq1 /usr/local/bin/sq1
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

echo "Running composer install..."
composer install -a

echo "Symlinking sq1 binary to /usr/local/bin/sq1, enter your password when requested."
symlink_sq1

sq1
echo ""
echo "************************************"
echo "If everything went smoothly, you should see the sq1 command list above. Reload your terminal to enable autocompletion."
echo "************************************"
