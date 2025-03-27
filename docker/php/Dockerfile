FROM php:8.3-fpm-alpine

# Establecer el directorio de trabajo
WORKDIR /var/www

# Instalar dependencias necesarias
RUN apk add --no-cache \
    postgresql-client msmtp perl wget procps shadow libzip libpng libjpeg-turbo libwebp freetype icu postgresql-dev && \
    apk add --no-cache --virtual build-deps \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd pdo_pgsql pgsql intl bcmath opcache exif zip && \
    apk del build-deps && rm -rf /usr/src/php*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
