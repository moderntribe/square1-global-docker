#!/usr/bin/env bash

#############################################################
# SquareOne Global Docker Installer
#############################################################

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
PHAR_NAME="so.phar"
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

enable_bash_autocomplete() {
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sudo curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion' -o $(brew --prefix)/etc/bash_completion.d/squareone.autocompletion
    curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion.zsh' -o ~/.squareone_completion.zsh && echo "source ~/.squareone_completion.zsh" >> ~/.zshrc
  else
    sudo curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion' -o /etc/bash_completion.d/squareone.autocompletion
    curl -s 'https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/squareone.autocompletion.fish' -o ~/.config/fish/completions/so.fish
  fi
}

install_phar() {
  PHAR_DOWNLOAD=$(curl -s https://api.github.com/repos/moderntribe/square1-global-docker/releases/latest \
        | grep browser_download_url \
        | grep ${PHAR_NAME} \
        | cut -d '"' -f 4)

  if [[ -z "$PHAR_DOWNLOAD" ]] ; then
    echo 'Error connecting to the GitHub API, enter a GitHub token to try again (you can create a new one here https://github.com/settings/tokens/new):';
    read GITHUB_TOKEN

    PHAR_DOWNLOAD=$(curl -H "Authorization: token $GITHUB_TOKEN" -s https://api.github.com/repos/moderntribe/square1-global-docker/releases/latest \
      | grep browser_download_url \
      | grep ${PHAR_NAME} \
      | cut -d '"' -f 4)

    if [[ -z "$PHAR_DOWNLOAD" ]] ; then
      echo "Whoops, we still can't connect. Try manually downloading so.phar from the releases page: https://github.com/moderntribe/square1-global-docker/releases"
      exit 1;
    fi
  fi

  curl -s -L --create-dirs "${PHAR_DOWNLOAD}" -o ${CONFIG_DIR}/bin/so
  chmod +x ${CONFIG_DIR}/bin/so
  sudo ln -s ${CONFIG_DIR}/bin/so /usr/local/bin/so
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
  curl -s https://raw.githubusercontent.com/moderntribe/square1-global-docker/master/brew/packages.txt -o ${CONFIG_DIR}/packages.txt
  brew install $(<"${CONFIG_DIR}"/packages.txt)
  echo "Setting the default PHP version to 7.4..."
  brew link php@7.4 --force
fi

echo "Creating config folder: ~/.config/squareone"
create_config_folder

echo "Enabling SquareOne autocompletion, enter your password when requested."
enable_bash_autocomplete

echo "Downloading so.phar to /usr/local/bin/so, enter your password when requested."
install_phar

# run SquareOne Global docker
so

echo ""
echo "************************************"
echo "If everything went smoothly, you should see the 'so' command list above. Reload your terminal to enable autocompletion."
echo "************************************"
