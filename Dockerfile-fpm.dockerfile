FROM git.nanobyte.cz/ovlach-public/php-docker:php-fpm-dev-8.4.17-cad44e8
ARG UID=1000
ARG GID=1000

USER 0:0

RUN usermod -u $UID www-data && \
    groupmod -g $GID www-data && \
    echo "UID=$UID GID=$GID"

RUN chown www-data:www-data /var/www

RUN echo "post_max_size = 100M\nupload_max_filesize = 100M\n" > /usr/local/etc/php/conf.d/uploads-fpm.ini

RUN apt-get update && apt-get install -y postgresql-client && rm -rf /var/lib/apt/cache/*

USER $UID:$GID

RUN composer global require laravel/installer
ENV PATH="/var/www/.composer/vendor/bin:${PATH}"

WORKDIR /var/www/html/
