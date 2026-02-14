FROM php:8.2

RUN apt-get update && apt-get install -y zlib1g-dev libzip-dev libonig-dev unzip
RUN docker-php-ext-install zip mbstring
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENTRYPOINT ["/data/.docker/entrypoint.sh"]
CMD ["tests"]