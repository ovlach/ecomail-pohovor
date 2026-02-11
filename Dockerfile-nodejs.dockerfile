FROM node:20-slim

ARG UID=1000
ARG GID=1000

RUN usermod -u $UID node && \
    groupmod -g $GID node && \
    echo "UID=$UID GID=$GID"

RUN mkdir -p /var/www/html && chown node:node /var/www/html
WORKDIR /var/www/html

COPY package*.json /var/www/html/
RUN chown -R node:node /var/www/html

USER node:node

RUN ls -lah /var/www/html && id && npm install

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
