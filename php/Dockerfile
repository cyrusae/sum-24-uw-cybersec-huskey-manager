FROM php:8.3-rc-fpm-alpine as backend-php-server

# Useful PHP extension installer image, copy binary into your container
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install php extensions
# exit on errors, exit on unset variables, print every command as it is executed
RUN set -eux; \
    install-php-extensions opcache mysqli;

# RUN docker-php-ext-install pdo pdo_mysql

# allow super user - set this if you use Composer as a
# super user at all times like in docker containers
ENV COMPOSER_ALLOW_SUPERUSER=1

# obtain composer using multi-stage build
# https://docs.docker.com/build/building/multi-stage/
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

#Here, we are copying only composer.json and composer.lock (instead of copying the entire source)
# right before doing composer install.
# This is enough to take advantage of docker cache and composer install will
# be executed only when composer.json or composer.lock have indeed changed!-
# https://medium.com/@softius/faster-docker-builds-with-composer-install-b4d2b15d0fff
COPY ./webapp/composer.* ./

# install
RUN composer install --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# copy application files to the working directory
COPY ./webapp .



#RUN chmod 766 /var/www/html/public/vaults/uploads 
# owner has read and write permissions (no execute) 
# TODO: do I want to allow execution? Will I have regrets
# tentatively added execution 
## File group has read and write permissions 
## TODO: Should they have write?
### Default is read-write
# updated to see if that helps with W6 lab
RUN chmod 777 /var/www/html/public/vaults/uploads 

# run composer dump-autoload --optimize
RUN composer dump-autoload --optimize


#RUN chmod 1770 /var/run/mysqld 
#attempt to fix error about access to this 
#okay look if you're going to error out with the claim it doesn't exist what do you want from me!!! 