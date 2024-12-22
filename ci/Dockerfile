FROM php:8.4-fpm-alpine

ENV USER=appuser
ENV GROUPNAME=$USER
ENV UID=12345
ENV GID=23456

RUN addgroup \
    --gid "$GID" \
    "$GROUPNAME" \
&&  adduser \
    --disabled-password \
    --gecos "" \
    --home "$(pwd)" \
    --ingroup "$GROUPNAME" \
    --no-create-home \
    --uid "$UID" \
    $USER

RUN apk add --no-cache \
    nodejs \
    yarn \
    oniguruma-dev \
    caddy \
    && docker-php-ext-install bcmath ctype fileinfo mbstring pdo_mysql gd \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./ci/entrypoint.sh /etc/entrypoint.sh
RUN chmod +x /etc/entrypoint.sh

WORKDIR /srv

COPY --chown=appuser:appuser . .

RUN composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && chown appuser:appuser storage bootstrap/cache \
    && yarn install --frozen-lockfile \
    && yarn run build

# Expose port 8080
EXPOSE 8080

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]
