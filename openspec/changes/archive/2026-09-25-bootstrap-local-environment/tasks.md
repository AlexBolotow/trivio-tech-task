# Tasks: Bootstrap local development environment

- [x] Initialize the Laravel application at the repository root without overwriting project documentation.
- [x] Pin compatible PHP, Composer, Nginx and MySQL image versions.
- [x] Add the PHP-FPM Dockerfile and development configuration.
- [x] Add Nginx configuration for Laravel `public/` and PHP-FPM.
- [x] Add `compose.yaml` with PHP-FPM, Nginx and MySQL healthchecks and volumes.
- [x] Add safe root `.env.example` values and document Laravel environment initialization.
- [x] Add Make targets for startup, shutdown, logs, shell, tests and an explicitly destructive reset.
- [x] Add the `/health` JSON endpoint.
- [x] Add README instructions for clean startup and verification.
- [x] Build the images and start the environment from a clean state.
- [x] Verify container health and the HTTP response.
- [x] Verify ordinary restart preserves MySQL data.
- [x] Review implementation, tests and documentation against this specification.

## Verification

Verified on 2026-09-25:

- `docker compose config --quiet` completed successfully.
- `make up` built the development image, installed dependencies, applied migrations and reported all services healthy.
- `curl --fail http://127.0.0.1:8080/health` returned `{"service":"trivio-tech-task","status":"ok"}`.
- `make test` passed 2 tests with 3 assertions.
- `composer validate --strict` completed successfully inside the app container.
- Xdebug was loaded with `XDEBUG_MODE=off`.
- After `make down` and `make up`, Laravel reported `Nothing to migrate`, confirming that the MySQL named volume persisted.
