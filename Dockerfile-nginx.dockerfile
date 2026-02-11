FROM docker.io/nginx:1.25.3-alpine
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

ARG UID=1000
ARG GID=1000

USER 0:0

RUN apk add shadow
RUN usermod -u $UID nginx && \
    groupmod -g $GID nginx && \
    echo "UID=$UID GID=$GID"

EXPOSE 80
