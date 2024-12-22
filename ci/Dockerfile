# FROM php:8.4-fpm-alpine

# ENV USER=appuser
# ENV GROUPNAME=$USER
# ENV UID=12345
# ENV GID=23456

# RUN addgroup \
#     --gid "$GID" \
#     "$GROUPNAME" \
# &&  adduser \
#     --disabled-password \
#     --gecos "" \
#     --home "$(pwd)" \
#     --ingroup "$GROUPNAME" \
#     --no-create-home \
#     --uid "$UID" \
#     $USER

# RUN apk add --no-cache \
#     nodejs \
#     yarn \
#     oniguruma-dev \
#     jpeg-dev \
#     libpng-dev \
#     zlib-dev \
#     caddy \
#     && docker-php-ext-install bcmath ctype fileinfo mbstring pdo_mysql gd \
#     && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# COPY ./ci/entrypoint.sh /etc/entrypoint.sh
# RUN chmod +x /etc/entrypoint.sh

# WORKDIR /srv

# COPY --chown=appuser:appuser . .

# RUN composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist \
#     && php artisan config:cache \
#    && php artisan route:cache \
#     && php artisan view:cache \
#     && chown appuser:appuser storage bootstrap/cache \
#     && yarn install --frozen-lockfile \
#     && yarn run build

# Expose port 8080

FROM php:8.4-alpine AS php-builder

RUN apk update --no-cache \
    && apk upgrade --no-cache \
    && apk add --no-cache \
        nodejs \
        yarn \
        oniguruma-dev \
        jpeg-dev \
        libpng-dev \
        zlib-dev \
        icu-dev \
        caddy \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#####################################
# PHP Extensions
#####################################

RUN docker-php-ext-configure gd --with-jpeg

RUN docker-php-ext-install bcmath ctype fileinfo mbstring pdo_mysql gd intl

WORKDIR /app

COPY . ./
RUN composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist


FROM node:lts-alpine AS node-builder

RUN apk add --no-cache yarn
WORKDIR /app
COPY . ./
RUN yarn install --frozen-lockfile && yarn build


#####################################
# Copy extensions from build stage
#####################################

FROM php:8.4-fpm-alpine

WORKDIR /srv

COPY . ./

COPY --from=node-builder /app/public/build/ /srv/public

COPY --from=php-builder /usr/local/lib/php/extensions/no-debug-non-zts-20240924/* /usr/local/lib/php/extensions/no-debug-non-zts-20240924
COPY --from=php-builder /usr/local/etc/php/conf.d/* /usr/local/etc/php/conf.d
COPY --from=php-builder /app/vendor /srv

COPY ./ci/entrypoint.sh /etc/entrypoint.sh
RUN chmod +x /etc/entrypoint.sh

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]
