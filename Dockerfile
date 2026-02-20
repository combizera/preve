############################################
# ARGs
############################################
ARG PHP_VARIATION=fpm-nginx
ARG CLI_VARIATION=cli
ARG PHP_VERSION=8.4
ARG BASE_OS=alpine
ARG CLI_IMAGE=serversideup/php:${PHP_VERSION}-${CLI_VARIATION}-${BASE_OS}
ARG FULL_IMAGE=serversideup/php:${PHP_VERSION}-${PHP_VARIATION}-${BASE_OS}
ARG NODE_VERSION=22
ARG NODE_IMAGE=node:${NODE_VERSION}-${BASE_OS}

############################################
# PHP - Vendor Image
############################################
FROM ${CLI_IMAGE} AS vendor

USER root
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-progress --no-interaction --optimize-autoloader --ignore-platform-reqs

############################################
# PHP - Build Laravel
############################################
FROM ${CLI_IMAGE} AS build

USER root
WORKDIR /var/www/html

COPY . .
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/logs \
    && chmod -R 755 storage bootstrap/cache

USER www-data

############################################
# PHP - Development Image with FPM and NGINX
############################################
FROM ${FULL_IMAGE} AS php-development

# We can pass USER_ID and GROUP_ID as build arguments
# to ensure the www-data user has the same UID and GID
# as the user running Docker.
ARG USER_ID
ARG GROUP_ID

# Switch to root so we can set the user ID and group ID
USER root
WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html
COPY --chmod=755 .docker/entrypoint.d/ /etc/entrypoint.d/

# Set the user ID and group ID for www-data
RUN docker-php-serversideup-set-id www-data $USER_ID:$GROUP_ID \
    && docker-php-serversideup-set-file-permissions --owner $USER_ID:$GROUP_ID --service nginx \
    && chmod -R 755 storage bootstrap/cache

# Drop privileges back to www-data
USER www-data
EXPOSE 8080 8443

STOPSIGNAL SIGQUIT
CMD ["/init"]

HEALTHCHECK --interval=5s --timeout=3s --retries=3 \
    CMD ["sh", "-c", "curl --insecure --silent --location --show-error --fail http://localhost:8080$HEALTHCHECK_PATH || exit 1"]

############################################
# Node.js - Development Image
############################################
FROM ${NODE_IMAGE} AS node-development

# We can pass USER_ID and GROUP_ID as build arguments
# to ensure the node user has the same UID and GID
# as the user running Docker.
ARG USER_ID
ARG GROUP_ID

# Switch to root so we can set the user ID and group ID
USER root
RUN apk add --no-cache shadow

# Script to handle existing group ID
RUN if getent group "$GROUP_ID" > /dev/null; then \
        moved_group_id="99$GROUP_ID"; \
        existing_group_name=$(getent group "$GROUP_ID" | cut -d: -f1); \
        echo "Moving GID of $existing_group_name to $moved_group_id..."; \
        groupmod -g "$moved_group_id" "$existing_group_name"; \
    fi

# Script to handle existing user ID
RUN if getent passwd "$USER_ID" > /dev/null; then \
        moved_user_id="99$USER_ID"; \
        existing_username=$(getent passwd "$USER_ID" | cut -d: -f1); \
        echo "Moving UID of $existing_username to $moved_user_id..."; \
        usermod -u "$moved_user_id" "$existing_username"; \
    fi

# Set the user ID and group ID for the node user
RUN groupmod -g $GROUP_ID node && usermod -u $USER_ID -g $GROUP_ID node

# Drop privileges back to node user
USER node

############################################
# Node.js - Build Image
############################################
FROM ${NODE_IMAGE} AS node-build

WORKDIR /usr/src/app/

COPY --chown=node:node . /usr/src/app/

RUN npm ci
RUN npm run build

# ############################################
# App - Production Image
# ############################################
FROM ${FULL_IMAGE} AS production

WORKDIR /var/www/html

COPY --chown=www-data:www-data --from=build /var/www/html /var/www/html
COPY --chown=www-data:www-data --from=node-build /usr/src/app/public/build /var/www/html/public/build
COPY --chmod=755 .docker/entrypoint.d/ /etc/entrypoint.d/

# Default to running the Laravel Automations with serversideup/php
ENV AUTORUN_ENABLED="false"

USER www-data

EXPOSE 8080 8443

STOPSIGNAL SIGQUIT
CMD ["/init"]

HEALTHCHECK --interval=5s --timeout=3s --retries=3 \
    CMD ["sh", "-c", "curl --insecure --silent --location --show-error --fail http://localhost:8080$HEALTHCHECK_PATH || exit 1"]

# ############################################
# PHP - CLI image for Workers and Schedulers
# ############################################
FROM ${CLI_IMAGE} AS cli

USER root
WORKDIR /var/www/html

COPY --from=build /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/logs \
    && chmod -R 755 storage bootstrap/cache

USER www-data
