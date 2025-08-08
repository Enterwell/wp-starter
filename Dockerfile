# Base stage
# PHP image based on Debian
FROM php:8.4-fpm-bookworm AS base

ENV EW_WP_VERSION="6.8.2"

# Install additional packages
RUN apt-get update \
    && apt-get install -y \
    libmagickwand-dev \
    libmagickcore-dev \
    libzip-dev \
    libwebp-dev \
    libavif-dev \
    gettext \
    unzip \
    nginx \
    supervisor \
    dialog \
    openssh-server \
    redis-server

# Install additional PHP modules with pecl
RUN pecl install \
    igbinary \
    imagick \
    redis \
    xdebug

# Install addiitonal PHP modules with docker-php-ext-install
RUN docker-php-ext-install \
    mysqli \
    exif \
    intl \
    zip

# Enable newly installed PHP modules
RUN docker-php-ext-enable \
    exif \
    igbinary \
    imagick \
    intl \
    mysqli \
    zip \
    redis

# Install WP-CLI
RUN set -ex; \
    curl -O "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar" && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

# Nginx configuration
COPY .infra/config/nginx.conf /etc/nginx/sites-available/default

# Supervisor configuration
COPY .infra/config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir /etc/supervisor/conf.d/extra

# Download Wordpress
RUN set -ex; \
    wp core download \
    --path=/var/www/html \
    --version=${EW_WP_VERSION} \
    --skip-content \
    --allow-root && \
    chown -R www-data:www-data /var/www/html

# WP config configuration
COPY --chown="www-data:www-data" --chmod=440 .infra/config/wp-config.php /var/www/html

# Download Wordpress plugins
RUN set -ex; \
    for plugin in better-wp-security.9.3.10 fluent-smtp.2.2.90 w3-total-cache.2.8.10 wordpress-seo.25.6; do \
        curl -fSL "https://downloads.wordpress.org/plugin/${plugin}.zip" -o "/tmp/${plugin}.zip"; \
        unzip "/tmp/${plugin}.zip" -d /var/www/html/wp-content/plugins/; \
        rm "/tmp/${plugin}.zip"; \
    done && \
    chown -R www-data:www-data /var/www/html/wp-content/plugins

# Copy and set permissions for the entrypoint script
COPY .infra/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Application runtimes and builders
FROM base AS application

# Install composer
COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

# Install node
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN echo '#!/bin/sh\nnode /usr/local/lib/node_modules/npm/bin/npm-cli.js "$@"' > /usr/local/bin/npm && \
    chmod +x /usr/local/bin/npm

# Install yarn
RUN npm install -g yarn

# Development setup
FROM application AS development-setup

# Copy files to temp location so that packages can be built
COPY plugins/ewplugin/composer.json plugins/ewplugin/composer.lock /tmp/ewplugin/
COPY themes/ew-theme/composer.json themes/ew-theme/composer.lock themes/ew-theme/package.json themes/ew-theme/yarn.lock /tmp/ew-theme/

# Navigate to plugin
WORKDIR /tmp/ewplugin

# Install composer packages for plugin
RUN composer install --no-interaction --prefer-dist

# Navigate to theme
WORKDIR /tmp/ew-theme

# Install composer packages for theme
RUN composer install --no-interaction --prefer-dist

# Install yarn packages for theme
RUN yarn install

# Enable development PHP modules
RUN docker-php-ext-enable \
    xdebug

# Supervisor dev configuration
COPY .infra/config/supervisord.dev.conf /etc/supervisor/conf.d/extra/supervisord.conf

# Nginx SSL dev configuration
COPY .infra/config/nginx.dev.conf /etc/nginx/sites-available/ssl.template
COPY .infra/certs/local.crt /etc/nginx/ssl/
COPY .infra/certs/local.key /etc/nginx/ssl/

# PHP configuration
COPY .infra/config/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Production setup
FROM application AS production-setup

# Run as www-data
USER www-data

# Copy source code
COPY plugins/ /var/www/html/wp-content/plugins/
COPY themes/ /var/www/html/wp-content/themes/

# Navigate to plugin
WORKDIR /var/www/html/wp-content/plugins/ewplugin

# Install composer packages for plugin
RUN composer install --no-dev --no-interaction --prefer-dist

# Navigate to plugin
WORKDIR /var/www/html/wp-content/themes/ew-theme

# Install composer packages for theme
RUN composer install --no-dev --no-interaction --prefer-dist

# Install yarn packages for theme
RUN yarn install

# Build frontend scripts for theme
RUN yarn build

# Development build
FROM development-setup AS development

ENV EW_DOMAIN_NAME="wp-starter.ew.local"
ENV EW_DISALLOW_FILE_EDIT=true
ENV EW_DISALLOW_FILE_MODS=false
ENV EW_AUTOMATIC_UPDATER_DISABLED=true
ENV EW_FS_METHOD="direct"
ENV EW_FORCE_SSL_ADMIN=true
ENV WP_ENVIRONMENT_TYPE="development"
ENV WP_DEBUG=true
ENV WP_DEBUG_DISPLAY=true
ENV WP_DEBUG_LOG=true
ENV WP_AUTO_UPDATE_CORE=false
ENV WP_MEMORY_LIMIT=128M
ENV WP_MAX_MEMORY_LIMIT=256M
ENV WP_CACHE=false

# Run as root
USER root

# Set website code location as workdir
WORKDIR /var/www/html

# Expose port
EXPOSE 443 10001

# Run entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Staging build
FROM base AS staging

ENV EW_DISALLOW_FILE_EDIT=true
ENV EW_DISALLOW_FILE_MODS=true
ENV EW_AUTOMATIC_UPDATER_DISABLED=true
ENV EW_FS_METHOD="ftpext"
ENV EW_FORCE_SSL_ADMIN=true
ENV WP_ENVIRONMENT_TYPE="staging"
ENV WP_DEBUG=false
ENV WP_DEBUG_DISPLAY=false
ENV WP_DEBUG_LOG=false
ENV WP_AUTO_UPDATE_CORE=false
ENV WP_MEMORY_LIMIT=128M
ENV WP_MAX_MEMORY_LIMIT=256M
ENV WP_CACHE=true

# Run as root
USER root

# Copy source code
COPY --chown="www-data:www-data" plugins/ /var/www/html/wp-content/plugins/
COPY --chown="www-data:www-data" themes/ /var/www/html/wp-content/themes/

# Other configurations
COPY .infra/config/sshd_config /etc/ssh/
COPY .infra/config/supervisord.prod.conf /etc/supervisor/conf.d/extra/supervisord.conf
COPY .infra/config/redis.conf /etc/redis/redis.conf

# Tidy up the source code
RUN rm -rf \
    /var/www/html/wp-content/plugins/ewplugin/tests \
    /var/www/html/wp-content/themes/ew-theme/.scripts \
    /var/www/html/wp-content/themes/ew-theme/webpack \
    /var/www/html/wp-content/themes/ew-theme/assets/js \
    /var/www/html/wp-content/themes/ew-theme/assets/styles

# Copy installed and built code
COPY --from=production-setup --chown="www-data:www-data" /var/www/html/wp-content/plugins/ewplugin/vendor /var/www/html/wp-content/plugins/ewplugin/vendor
COPY --from=production-setup --chown="www-data:www-data" /var/www/html/wp-content/themes/ew-theme/vendor /var/www/html/wp-content/themes/ew-theme/vendor
COPY --from=production-setup --chown="www-data:www-data" /var/www/html/wp-content/themes/ew-theme/assets/dist /var/www/html/wp-content/themes/ew-theme/assets/dist

# Setup SSH for Azure Portal access only
RUN echo "root:Docker!" | chpasswd
RUN mkdir /run/sshd

# Links persistent storage folder to Wordpress one
RUN ln -s /home/uploads /var/www/html/wp-content/uploads

# Set website code location as workdir
WORKDIR /var/www/html

# Expose port
EXPOSE 80 2222

# Run entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Production build
FROM base AS production

ENV EW_DISALLOW_FILE_EDIT=true
ENV EW_DISALLOW_FILE_MODS=true
ENV EW_AUTOMATIC_UPDATER_DISABLED=true
ENV EW_FS_METHOD="ftpext"
ENV EW_FORCE_SSL_ADMIN=true
ENV WP_ENVIRONMENT_TYPE="production"
ENV WP_DEBUG=false
ENV WP_DEBUG_DISPLAY=false
ENV WP_DEBUG_LOG=false
ENV WP_AUTO_UPDATE_CORE=false
ENV WP_MEMORY_LIMIT=128M
ENV WP_MAX_MEMORY_LIMIT=256M
ENV WP_CACHE=true

# Run as root
USER root

# Copy source code
COPY --chown="www-data:www-data" plugins/ /var/www/html/wp-content/plugins/
COPY --chown="www-data:www-data" themes/ /var/www/html/wp-content/themes/

# Other configurations
COPY .infra/config/sshd_config /etc/ssh/
COPY .infra/config/supervisord.prod.conf /etc/supervisor/conf.d/extra/supervisord.conf
COPY .infra/config/redis.conf /etc/redis/redis.conf

# Tidy up the source code
RUN rm -rf \
    /var/www/html/wp-content/plugins/ewplugin/tests \
    /var/www/html/wp-content/themes/ew-theme/.scripts \
    /var/www/html/wp-content/themes/ew-theme/webpack \
    /var/www/html/wp-content/themes/ew-theme/assets/js \
    /var/www/html/wp-content/themes/ew-theme/assets/styles

# Copy installed and built code
COPY --from=production-setup --chown="www-data:www-data" /var/www/html/wp-content/plugins/ewplugin/vendor /var/www/html/wp-content/plugins/ewplugin/vendor
COPY --from=production-setup --chown="www-data:www-data" /var/www/html/wp-content/themes/ew-theme/vendor /var/www/html/wp-content/themes/ew-theme/vendor
COPY --from=production-setup --chown="www-data:www-data" /var/www/html/wp-content/themes/ew-theme/assets/dist /var/www/html/wp-content/themes/ew-theme/assets/dist

# Setup SSH for Azure Portal access only
RUN echo "root:Docker!" | chpasswd
RUN mkdir /run/sshd

# Links persistent storage folder to Wordpress one
RUN ln -s /home/uploads /var/www/html/wp-content/uploads

# Set website code location as workdir
WORKDIR /var/www/html

# Expose port
EXPOSE 80 2222

# Run entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
