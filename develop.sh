#!/usr/bin/env sh

set -ex

cp .env.example .env

cat > docker-compose.override.yml <<EOF
services:
  nginx:
    build:
      args:
        UID: $(id -u)
        GID: $(id -g)
  php-fpm:
    build:
      args:
        UID: $(id -u)
        GID: $(id -g)
  queue-worker:
    build:
      args:
        UID: $(id -u)
        GID: $(id -g)
  nodejs:
    build:
      args:
        UID: $(id -u)
        GID: $(id -g)
EOF

docker compose build

# composer
docker compose run --rm -it php-fpm composer install

# composer
docker compose run --rm -it nodejs npm install

# database
docker compose run --rm -it php-fpm /var/www/html/artisan migrate

# run containers (and build again)
if [ "$NO_EXECUTE" != "1" ]; then
  docker compose up --build
fi
