# Tasks: Bootstrap local development environment

- [ ] Initialize the Laravel application at the repository root without overwriting project documentation.
- [ ] Pin compatible PHP, Composer, Nginx and MySQL image versions.
- [ ] Add the PHP-FPM Dockerfile and development configuration.
- [ ] Add Nginx configuration for Laravel `public/` and PHP-FPM.
- [ ] Add `compose.yaml` with PHP-FPM, Nginx and MySQL healthchecks and volumes.
- [ ] Add safe root `.env.example` values and document Laravel environment initialization.
- [ ] Add Make targets for startup, shutdown, logs, shell, tests and an explicitly destructive reset.
- [ ] Add the `/health` JSON endpoint.
- [ ] Add README instructions for clean startup and verification.
- [ ] Build the images and start the environment from a clean state.
- [ ] Verify container health and the HTTP response.
- [ ] Verify ordinary restart preserves MySQL data.
- [ ] Review implementation, tests and documentation against this specification.
