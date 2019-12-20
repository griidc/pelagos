export SYMFONY_ENV=drupal_prod
cd /opt/pelagos
bin/console pelagos:account-expiration-notify >> /opt/pelagos/var/logs/password_notification_email.log
