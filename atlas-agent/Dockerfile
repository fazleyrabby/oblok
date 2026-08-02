FROM php:8.3-cli-alpine

RUN docker-php-ext-install curl

WORKDIR /atlas-agent

COPY bin bin
COPY src src

ENTRYPOINT ["php", "/atlas-agent/bin/atlas-agent"]
