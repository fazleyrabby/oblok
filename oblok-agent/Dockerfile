FROM php:8.3-cli-alpine

RUN docker-php-ext-install curl

WORKDIR /oblok-agent

COPY bin bin
COPY src src

ENTRYPOINT ["php", "/oblok-agent/bin/oblok-agent"]
