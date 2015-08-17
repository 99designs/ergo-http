FROM php:5.5

RUN apt-get update && apt-get install -y git && apt-get clean
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /bin/composer

RUN mkdir /src
WORKDIR /src

CMD []
