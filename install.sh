#!/usr/bin/env bash

#############################################################
# SquareOne Global Installer
#############################################################

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );

# Functions
install_homebrew() {
  bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"
}

enable_bash_autocomplete() {
  echo "source ${SCRIPTDIR}/bin/bash-autocomplete.sh" >> ~/.bashrc
  echo "source ${SCRIPTDIR}/bin/bash-autocomplete.sh" >> ~/.zshrc
  echo "source ${SCRIPTDIR}/bin/bash-autocomplete.sh" >> ~/.bash_profile
}

symlink_sq1() {
  sudo ln -s ${SCRIPTDIR}/sq1 /usr/local/bin/sq1
}

# OSX
if [[ "$OSTYPE" == "darwin"* ]]; then
  which -s docker
  if [[ $? != 0 ]] ; then
      echo "Docker appears to be missing, install it from here: https://hub.docker.com/editions/community/docker-ce-desktop-mac"
      exit 1;
  fi

  which -s brew
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

echo "Enabling sq1 autocompletion..."
enable_bash_autocomplete

echo "Symlinking sq1 binary to /usr/local/bin/sq1, enter your password when requested."
symlink_sq1

sq1
echo "If everything went smoothly, you should see the sq1 command list above. Reload your terminal to enable autocompletion."
