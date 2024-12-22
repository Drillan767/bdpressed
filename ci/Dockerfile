FROM php:8.4-fpm-alpine AS build

RUN apk add --no-cache \
    zip \
    libzip-dev \
    libjpeg-turbo \
    libpng \
    libjpeg-turbo-dev \
    libpng-dev \
    nodejs \
    yarn \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /srv

COPY . .

RUN composer install --no-dev --prefer-dist \
    && yarn install \
    && yarn build

RUN chown -R www-data:www-data /srv \
    && chmod -R 775 /srv/storage \
    && chmod -R 775 /srv/bootstrap/cache \
    && chown -R www-data:www-data /srv/vendor \
    && chmod -R 775 /srv/vendor

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    zip \
    libzip-dev \
    libjpeg-turbo \
    libpng \
    libjpeg-turbo-dev \
    libpng-dev \
    oniguruma-dev \
    caddy \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip pdo pdo_mysql \
    && docker-php-ext-configure gd --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-enable gd \
    && docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath \
    && docker-php-ext-install exif \
    && docker-php-ext-enable exif \
    && docker-php-ext-install opcache \
    && docker-php-ext-enable opcache \
    && rm -rf /var/cache/apk/*

COPY --from=build /srv /srv
COPY ./ci/php.ini "$PHP_INI_DIR/conf.d/app.ini"

WORKDIR /srv

COPY ./ci/entrypoint.sh /etc/entrypoint.sh
RUN chmod +x /etc/entrypoint.sh

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]
