FROM php:8.3-cli-alpine

WORKDIR /oblok-agent

COPY bin bin
COPY src src

ENTRYPOINT ["php", "/oblok-agent/bin/oblok-agent"]
