FROM php:7.2
MAINTAINER adam.stipak@gmail.com

RUN apt-get update && apt-get install -y zlib1g-dev
RUN docker-php-ext-install zip mbstring
RUN curl -sS https://getcomposer.org/installer |php -- --install-dir=/usr/local/bin --filename=composer

ENTRYPOINT ["/data/.docker/entrypoint.sh"]
CMD ["tests"]
