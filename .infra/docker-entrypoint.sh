#!/bin/sh

# Custom logic when development env
if [ "$WP_ENVIRONMENT_TYPE" = "development" ]; then
  # Copy composer and node packages to mounted volume
  if [ ! -d /var/www/html/wp-content/plugins/ewplugin/vendor ]; then
    cp -r /tmp/ewplugin/vendor /var/www/html/wp-content/plugins/ewplugin/
  fi

  if [ ! -d /var/www/html/wp-content/themes/ew-theme/vendor ]; then
    cp -r /tmp/ew-theme/vendor /var/www/html/wp-content/themes/ew-theme/
  fi

  if [ ! -d /var/www/html/wp-content/themes/ew-theme/node_modules ]; then
    cp -r /tmp/ew-theme/node_modules /var/www/html/wp-content/themes/ew-theme/
  fi

  # Set default values if not provided
  export EW_DOMAIN_NAME="${EW_DOMAIN_NAME:=localhost}"

  # Setup nginx conf based on environment
  envsubst "${EW_DOMAIN_NAME}" < /etc/nginx/sites-available/ssl.template > /etc/nginx/sites-available/ssl
  ln -s /etc/nginx/sites-available/ssl /etc/nginx/sites-enabled/ssl
fi

# Start supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
