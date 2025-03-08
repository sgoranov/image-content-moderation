FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    build-essential \
    make \
    gcc \
    gfortran \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    php \
    php-dev \
    php-gd \
    libopenblas-dev \
    liblapacke-dev \
    re2c \
    curl \
    wget \
    git \
    nano

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Install PECL Tensor extension
RUN pecl install tensor
RUN echo "extension=tensor.so" >> /etc/php/8.1/cli/php.ini \
    && echo "extension=tensor.so" >> /etc/php/8.1/apache2/php.ini

CMD [ "bash" ]