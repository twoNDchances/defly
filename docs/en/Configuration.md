# Configuration

Defly uses a root `.env` for [Docker Compose](Installation.md#docker-compose) and service-specific `.env` files for manual execution. Never commit real secrets. See [Environment Variables](Environment-Variables.md) for every variable, default, and constraint.

## Docker Compose Configuration

The root `.env` controls images, the database, ports, credentials, and values passed into services.

### Project and Images

| Variable | Meaning |
| --- | --- |
| `COMPOSE_PROJECT_NAME` | Prefix for Compose networks, volumes, and labels; default `defly`. |
| `MANAGER_IMAGE` | Manager image. |
| `ORCHESTRATOR_IMAGE` | Orchestrator image. |
| `SERVER_DEFENDER_IMAGE` | Image used to create [Defenders](CoreConcepts/Defender.md). |

The primary network is named `${COMPOSE_PROJECT_NAME}_infrastructure`.

### Database

All services must point to the same database:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
MARIADB_ROOT_PASSWORD
```

Manager uses `DB_USERNAME`/`DB_PASSWORD`; Compose maps them to the names expected by Orchestrator and Defender.

### Manager and Bootstrap User

```text
APP_NAME
APP_ENV
APP_KEY
APP_DEBUG
APP_URL
APP_LOCALE
MANAGER_HTTP_PORT
MANAGER_HTTPS_PORT
USER_NAME
USER_EMAIL
USER_PASSWORD
```

Set `APP_DEBUG=false` and use a stable `APP_KEY` in production.

### Worker

```text
WORKER_TRIES
WORKER_TIMEOUT
WORKER_MAX_TIME
```

Worker handles deployment, cancellation, and log-following jobs. Its timeout must allow Docker to obtain, create, and start a container without hiding a genuinely stuck job.

## Manager

`manager/.env` configures Laravel, the database, mail, APIs, and calls to Orchestrator and Defender.

### Orchestrator Connection

```text
ORCHESTRATOR_BASE_URL
ORCHESTRATOR_USERNAME
ORCHESTRATOR_PASSWORD
ORCHESTRATOR_TLS_SKIP_VERIFY
ORCHESTRATOR_TLS_CERT_FILE
ORCHESTRATOR_PATH_PREFIX
ORCHESTRATOR_PATH_DEPLOYMENT
ORCHESTRATOR_METHOD_DEPLOY
ORCHESTRATOR_METHOD_FOLLOW
ORCHESTRATOR_METHOD_CANCEL
```

The username and password must match Orchestrator's `SERVER_USERNAME` and `SERVER_PASSWORD`. Paths and methods on both sides must describe the same API contract.

### Defender Connection

```text
DEFENDER_SERVER_TLS_SKIP_VERIFY
DEFENDER_SERVER_TLS_DIRECTORY
```

When verification is enabled, Manager looks up a certificate by Defender name in the configured directory.

### Manager API

```text
TOKEN_LOCATION
TOKEN_KEY_NAME
USER_AGENT
API_PREFIX
GUI_PREFIX
```

The default API prefix is `v1`, the UI prefix is `defly-manager`, and the default [Key](CoreConcepts/Key.md) header is `X-Token-Key`.

### Mail

```text
MAIL_MAILER
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
RESEND_API_KEY
RESEND_DOMAIN
RESEND_PATH
```

Configure Resend only when using its mailer. Webhooks require a separate secret in Manager.

## Orchestrator

`orchestrator/.env` configures Django, the database, Docker, and the API receiving requests from Manager.

```text
SECRET_KEY_FILE
ALLOWED_HOSTS
DB_HOST
DB_PORT
DB_USER
DB_PASS
DB_NAME
SERVER_MANAGER
SERVER_USERNAME
SERVER_PASSWORD
SERVER_EMAIL_HEADER_KEY
SERVER_PATH_PREFIX
SERVER_PATH_DEPLOYMENT
SERVER_METHOD_DEPLOY
SERVER_METHOD_FOLLOW
SERVER_METHOD_CANCEL
SERVER_DEFENDER_IMAGE
SERVER_DEFENDER_TLS_VOLUME
SERVER_DOCKER_BASE_URL
```

`SERVER_MANAGER` limits allowed callers. `SERVER_DOCKER_BASE_URL` grants Docker control and must be protected like a privileged credential.

## Defender

Defender has three configuration groups: common, control server, and proxy. During deployment, values are assembled from the Defender record and system configuration.

### Common Variables

```text
DATABASE_HOST
DATABASE_PORT
DATABASE_NAME
DATABASE_USER
DATABASE_PASS
DEFENDER_NAME
```

Other common settings control error storage, Wordlists, and runtime data.

### Control Server

The control server exposes its API on port `9947` by default. Its variables configure addressing, TLS, logging, and the trusted Manager. The Manager form defaults `SERVER_SECURITY_MANAGER` to `worker` so control jobs originate from the correct process.

### Proxy

```text
PROXY_BACKEND_URL
```

The proxy listens on port `9948` by default and forwards application traffic to the backend. Other variables control TLS, timeouts, logging, health monitoring, and severity scores.

The `info`, `notice`, `warning`, `error`, `critical`, `alert`, and `emergency` scores are used by the [`suspect` Action](CoreConcepts/Action.md#suspect).

## TLS, Volumes, and Networks

- Defender TLS uses `${COMPOSE_PROJECT_NAME}_${SERVER_DEFENDER_TLS_VOLUME}`.
- Each Defender receives separate log and error volumes.
- Static Compose services and dynamic Defenders use `${COMPOSE_PROJECT_NAME}_infrastructure`.
- The proxy port comes from the Defender record and is published on the Docker host.

## Checks After Configuration Changes

1. Compare contract variables on both Manager and Orchestrator.
2. Run `docker compose config` to inspect the rendered Compose configuration.
3. Restart the affected service.
4. Redeploy a Defender when its environment or volumes change.
5. Check logs and health before sending real traffic.
