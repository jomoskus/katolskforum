#!/usr/bin/env bash
# Setup script for cloud agent VMs (referenced by .cursor/environment.json).
# Installs PHP, Composer and project dependencies, then prepares the app.
set -euo pipefail

if ! command -v php > /dev/null; then
    sudo apt-get update -qq
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
        php-cli php-mbstring php-xml php-curl php-sqlite3 php-zip \
        php-intl php-gd php-bcmath
fi

# pcov is required for the coverage gates.
if ! php -m | grep -qi pcov; then
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq php-pcov \
        || sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq php8.3-pcov
fi

if ! command -v composer > /dev/null; then
    curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
    sudo php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm /tmp/composer-setup.php
fi

composer setup
