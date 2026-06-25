FROM php:8.4-fpm-bookworm

ARG UID=1000
ARG GID=1000

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd -g ${GID} saliksik \
    && useradd -u ${UID} -g saliksik -m saliksik \
    && mkdir -p /var/www/html \
    && chown -R saliksik:saliksik /var/www/html

WORKDIR /var/www/html

USER saliksik

EXPOSE 9000

CMD ["php-fpm"]
