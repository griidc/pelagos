#!/bin/bash

# Install Pelagos
#git clone https://github.com/griidc/pelagos /opt/pelagos
#cd /opt/pelagos
#git checkout develop

#composer install
#yarn install
#npx update-browserslist-db@latest
#yarn dev

# Update /etc/php.ini
sed -i 's/memory_limit = 128M/memory_limit = 2048M/g' /etc/php.ini
sed -i 's/max_input_time = 60/max_input_time = 1800/g' /etc/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 1800/g' /etc/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 100M/g' /etc/php.ini
sed -i 's/max_file_uploads = 20/max_file_uploads = 1000/g' /etc/php.ini
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 11M/g' /etc/php.ini
sed -i 's/;date.timezone =/date.timezone = America\/Chicago/g' /etc/php.ini
sed -i 's/;date.default_latitude = 31.7667/date.default_latitude = 27.7111126/g' /etc/php.ini
sed -i 's/;date.default_longitude = 35.2333/date.default_longitude = -97.3246478/g' /etc/php.ini
