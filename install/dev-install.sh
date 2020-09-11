#!/usr/bin/env bash

#############################################################
# SquareOne Global Docker Dev Installer
#############################################################

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );


# Functions
create_config_folder() {
  mkdir -p ~/.config/squareone
}

install_homebrew() {
  bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"
}

enable_bash_autocomplete() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sudo cp -f ${SCRIPTDIR}/../squareone.autocompletion $(brew --prefix)/etc/bash_completion.d/squareone.dev.autocompletion
    cp -f ${SCRIPTDIR}/../squareone.autocompletion ~/.squareone_dev_completion && echo "source ~/.squareone_dev_completion" >> ~/.zshrc
  else
    sudo cp -f ${SCRIPTDIR}/../squareone.autocompletion /etc/bash_completion.d/squareone.dev.autocompletion
  fi
}

symlink_sq1() {
  sudo ln -s ${SCRIPTDIR}/../so /usr/local/bin/sodev
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

echo "Creating config folder: ~/.config/squareone"
create_config_folder

echo "Enabling SquareOne autocompletion, enter your password when requested."
enable_bash_autocomplete

echo "Running composer install..."
composer install -d ${SCRIPTDIR}/../

echo "Symlinking so binary to /usr/local/bin/so, enter your password when requested."
symlink_sq1

sodev

echo ""
echo "************************************"
echo "If everything went smoothly, you should see the so command list above. Reload your terminal to enable autocompletion."
echo "************************************"
