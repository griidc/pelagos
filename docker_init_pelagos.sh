#!/bin/bash

# Install Pelagos
git clone https://github.com/griidc/pelagos /opt/pelagos
cd /opt/pelagos
git checkout develop

cp /.env.local /opt/pelagos

# Install Pelagos dependencies
composer install
yarn install
npx update-browserslist-db@latest
yarn dev

# Load Data
printf 'y\ny\ny\nn\n' | bin/loaddump /opt/newest-pelagos.sql

# Start Webserver
symfony server:start --port=8080 --allow-all-ip --no-tls

