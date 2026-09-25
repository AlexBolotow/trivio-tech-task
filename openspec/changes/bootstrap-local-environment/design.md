# Design: Bootstrap local development environment

## Proposed layout

Laravel lives at the repository root. Infrastructure-specific files live under `docker/`; `compose.yaml`, `.env.example`, `Makefile` and `README.md` remain at the root.

```text
.
├── app/                     # Laravel application namespace
├── bootstrap/
├── config/
├── docker/
│   ├── nginx/
│   └── php/
├── compose.yaml
├── Dockerfile
├── Makefile
└── README.md
```

The Laravel root layout is preferred over an additional `backend/` or `app/` wrapper because this repository contains one backend application and Laravel tooling expects the project root.

## Services

### PHP-FPM

- Multi-stage Dockerfile with a reusable PHP base and a development target.
- Composer is copied from the official Composer image.
- Development target contains Xdebug, disabled by default unless explicitly enabled.
- Container runs application commands; host PHP/Composer are not prerequisites.

### Nginx

- Serves Laravel's `public/` directory.
- Passes PHP requests to PHP-FPM.
- Exposes a configurable host HTTP port.

### MySQL

- MySQL 8.4 LTS family.
- Named volume for persistent data.
- Healthcheck used by dependent services.
- Credentials and optional host port come from the local root `.env` created from `.env.example`.

## Startup flow

```text
make up
→ validate/create required local configuration
→ build images
→ start MySQL
→ install Composer dependencies inside container when needed
→ start PHP-FPM and Nginx
→ wait for healthchecks
→ print the application URL
```

## Health endpoint

Add a minimal `/health` JSON route. It proves that Nginx can reach PHP-FPM and Laravel can boot. Database readiness remains the responsibility of the MySQL container healthcheck at this stage; a deep dependency health API can be added separately.

## Configuration boundaries

- Root `.env`: Docker Compose ports, project identity and database container credentials; ignored by Git.
- Root `.env.example`: safe documented defaults; committed.
- Laravel `.env`: generated locally from Laravel's example and configured to use service name `mysql`; ignored by Git.
- Secrets are never committed.

## Alternatives considered

### Laravel in a nested application directory

Useful for a monorepo with independent frontend/backend applications, but currently adds path and tooling complexity without a requirement.

### Laravel Sail

Provides a fast official baseline, but hides part of the Docker/Nginx/PHP-FPM setup that is an explicit learning subject in this project.

### Local PHP and Composer

Faster for some developers, but weakens reproducibility and creates host-version coupling.

## Risks and checks

- Apple Silicon image compatibility: select multi-architecture upstream images.
- File ownership: Composer-generated files must remain editable by the host user.
- Startup races: use healthchecks and explicit initialization rather than fixed sleeps.
- Destructive reset: expose a separate clearly named command and document that it removes the database volume.
