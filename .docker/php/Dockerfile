# Configurable packages
ARG PHP_VERSION=8.2
ARG ALPINE_VERSION=""

FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION}

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk update && apk upgrade

RUN apk add --update --no-cache \
    acl \
    bash \
    ca-certificates \
    git \
    openssh \
    rsync \
    patch \
    postgresql

RUN install-php-extensions intl curl dom fileinfo mbstring zip opcache exif apcu pdo_pgsql

ARG WITH_XDEBUG=0
ARG WITH_IMAGICK=0
ARG WITH_GD=0
ARG WITH_PDFTK=0
ARG WITH_WKHTMLTOPDF=0
ARG WITH_MONGODB=0
ARG WITH_REDIS=0
ARG WITH_FFMPEG=0
ARG WITH_AMQP=0
ARG WITH_PCNTL=0
ARG WITH_PDO_PGSQL=0
ARG WITH_CALENDAR=0
ARG WITH_PCOV=0
ARG WITH_OPENSSL=0
ARG WITH_MCRYPT=0
ARG WITH_LIBSODIUM=0
ARG WITH_SOAP=0

RUN if [[ "${WITH_XDEBUG}" -eq 1 ]] ; then install-php-extensions xdebug ; fi
RUN if [[ "${WITH_IMAGICK}" -eq 1 ]] ; then install-php-extensions imagick ; fi
RUN if [[ "${WITH_GD}" -eq 1 ]] ; then install-php-extensions gd ; fi
RUN if [[ "${WITH_MONGODB}" -eq 1 ]] ; then install-php-extensions mongodb ; fi
RUN if [[ "${WITH_REDIS}" -eq 1 ]] ; then install-php-extensions redis ; fi
RUN if [[ "${WITH_AMQP}" -eq 1 ]] ; then install-php-extensions amqp ; fi
RUN if [[ "${WITH_PCNTL}" -eq 1 ]] ; then install-php-extensions pcntl ; fi
RUN if [[ "${WITH_PDO_PGSQL}" -eq 1 ]] ; then install-php-extensions pdo_pgsql ; fi
RUN if [[ "${WITH_CALENDAR}" -eq 1 ]] ; then install-php-extensions calendar ; fi
RUN if [[ "${WITH_PCOV}" -eq 1 ]] ; then install-php-extensions pcov ; fi
RUN if [[ "${WITH_OPENSSL}" -eq 1 ]] ; then install-php-extensions openssl ; fi
RUN if [[ "${WITH_MCRYPT}" -eq 1 ]] ; then install-php-extensions mcrypt ; fi
RUN if [[ "${WITH_LIBSODIUM}" -eq 1 ]] ; then install-php-extensions libsodium ; fi
RUN if [[ "${WITH_SOAP}" -eq 1 ]] ; then install-php-extensions soap ; fi

ADD tools /root/tools
RUN if [[ "${WITH_PDFTK}" -eq 1 ]] ; then sh /root/tools/pdftk.sh ; fi
RUN if [[ "${WITH_WKHTMLTOPDF}" -eq 1 ]] ; then sh /root/tools/wkhtmltopdf.sh ; fi
RUN if [[ "${WITH_FFMPEG}" -eq 1 ]] ; then apk add --update --no-cache ffmpeg ; fi

# Configurable php configuration
ENV MEMORY_LIMIT "256M"

COPY ./php.ini $PHP_INI_DIR/conf.d/php.ini

WORKDIR /srv

CMD ["php-fpm", "-R", "-F"]
