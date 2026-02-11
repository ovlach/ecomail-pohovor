Getting started
===

* **Requirements:**
    * bash
    * Docker with Docker Compose v2

* **Run:**
    ```bash
    develop.sh
    ```
    * Enjoy your coffee :)
    * Laravel will serve the app at http://localhost:8000

---

Tests
---
Run tests:

```bash
docker compose exec php-fpm vendor/bin/phpunit
```

---

PHPStan & Code Style
---

* Run PHP CodeSniffer:
    ```bash
    docker compose exec php-fpm vendor/bin/phpcs
    ```
* Run PHPStan with increased memory limit:
    ```bash
    docker compose exec php-fpm vendor/bin/phpstan --memory-limit=2G
    ```

---

Containers
---

* `php-fpm` - FPM and additional tooling
* `nginx` - Serves static content, proxies requests to FPM
* `postgresql` - Database
* `postgresql-test` - Database for testing purposes

---

Future directions
---

* [ ] Sessions in Redis (or a better storage)
* [ ] Send XML by parts from frontend to importer
* [ ] The current tests are very basic. Add more scenarios.
* [ ] Notifications via websockets
* [ ] Store import state in Redis (no database polling)
* [ ] Implement observability (especially Prometheus for import time and flow, OTel, Loki, Grafana, etc.)
* [ ] Integrate a CI/CD pipeline.

---

Notes
---
