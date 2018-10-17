FROM php:cli-alpine

# Build-time metadata as defined at http://label-schema.org
ARG BUILD_DATE
ARG VCS_REF
ARG VERSION
LABEL maintainer="James Zhu <168262+fatindeed@users.noreply.github.com>" \
      org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.name="semi-data-crawler" \
      org.label-schema.description="Semiconductor data crawler" \
      org.label-schema.url="https://hub.docker.com/r/fatindeed/semi-data-crawler/" \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vcs-url="https://github.com/fatindeed/docker-semi-data-crawler" \
      org.label-schema.vendor="James Zhu" \
      org.label-schema.version=$VERSION \
      org.label-schema.schema-version="1.0"

ARG ALPINE_MIRROR

COPY src /var/www/html/

WORKDIR /var/www/html

RUN set -e; \
# Switch to a mirror if given
    if [ -n "$ALPINE_MIRROR" ]; then \
        ALPINE_MIRROR=${ALPINE_MIRROR//\//\\\/}; \
        sed -i "s/http:\/\/dl-cdn.alpinelinux.org/$ALPINE_MIRROR/g" /etc/apk/repositories; \
    fi; \
# Install build dependency packages
    apk update; \
    apk add --virtual .phpize-deps-configure $PHPIZE_DEPS libjpeg-turbo-dev libpng-dev; \
    docker-php-ext-configure gd --with-jpeg-dir=/usr/include/; \
    docker-php-ext-install gd pcntl; \
    docker-php-source delete; \
# Install build dependency packages
    runDeps="$( \
      scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
      | tr ',' '\n' \
      | sort -u \
      | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --virtual .php-rundeps $runDeps tzdata; \
# Cleanup
    rm -rf /tmp/pear; \
    rm -rf /usr/local/include; \
    rm -rf /var/cache/apk/*; \
# System configurations
    { \
        head -n -1 /usr/local/bin/docker-php-entrypoint; \
        echo 'if [ ! -z "$TIMEZONE" ]; then'; \
        echo '    cp "/usr/share/zoneinfo/${TIMEZONE}" /etc/localtime'; \
        echo '    echo "$TIMEZONE" > /etc/timezone'; \
        echo 'fi'; \
        echo ; \
        tail -n 1 /usr/local/bin/docker-php-entrypoint; \
    } | tee /tmp/docker-php-entrypoint; \
    chmod a+x /tmp/docker-php-entrypoint; \
    mv /tmp/docker-php-entrypoint /usr/local/bin/; \
# Install required libraries
    curl -sS https://getcomposer.org/installer | php; \
    php composer.phar install --no-dev; \
    rm composer.phar