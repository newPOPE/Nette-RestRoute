FROM php:5.6
MAINTAINER adam.stipak@gmail.com

RUN apt-get update
RUN docker-php-ext-install mbstring
RUN apt-get install -y zlib1g-dev
RUN docker-php-ext-install zip
RUN curl -sS https://getcomposer.org/installer |php -- --install-dir=/usr/local/bin --filename=composer

ENTRYPOINT ["/data/.docker/entrypoint.sh"]
CMD ["tests"]
