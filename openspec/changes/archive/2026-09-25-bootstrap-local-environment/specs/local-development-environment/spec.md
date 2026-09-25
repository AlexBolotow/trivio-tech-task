# Local development environment specification

## Requirement: Reproducible startup

The system SHALL start from a clean checkout using one documented command without requiring host PHP or Composer.

### Scenario: First startup

GIVEN Docker Desktop and Docker Compose are installed
AND the documented environment file has been created from its example
WHEN the developer runs the documented startup command
THEN application dependencies are installed
AND PHP-FPM, Nginx and MySQL become healthy
AND the application becomes reachable through the configured HTTP port.

## Requirement: Application health

The system SHALL expose a lightweight HTTP health endpoint for local infrastructure verification.

### Scenario: Healthy application

GIVEN all required containers are healthy
WHEN the developer requests the health endpoint
THEN the API returns HTTP 200
AND a stable JSON payload identifying the service and healthy status.

## Requirement: Configurable host resources

The system SHALL allow host-facing ports and development project identity to be configured without editing Compose files.

### Scenario: Default port is occupied

GIVEN the default HTTP or MySQL host port is unavailable
WHEN the developer changes the corresponding local environment variable
AND restarts the environment
THEN the services use the configured host ports.

## Requirement: Persistent development database

The system SHALL preserve MySQL data across ordinary container restarts.

### Scenario: Restart environment

GIVEN data has been written to MySQL
WHEN the developer stops and starts the environment without requesting a reset
THEN the data remains available.

## Requirement: Isolated initial scope

The initial environment SHALL NOT require Redis, RabbitMQ, Elasticsearch or frontend services to become healthy.
