# Defly Orchestrator

Defly Orchestrator is the Django ASGI service used by Defly Manager to deploy,
follow, and cancel Defender containers through Docker.

## Requirements

- Python 3.14+
- `uv`
- MySQL client/build support for `mysqlclient`
- Docker API access from the orchestrator process

## Setup

Install dependencies from the orchestrator directory:

```powershell
cd orchestrator
uv sync
```

Create a local environment file:

```powershell
Copy-Item .env.example .env
```

Generate the local Django secret key file:

```powershell
uv run python manage.py generatesecretkeyfile
```

TLS is optional for local development. Generate the local TLS certificate and
key only when you want to run the Orchestrator over HTTPS:

```powershell
uv run python manage.py generatetlsfile
```

Generated files are written to:

```text
storage/secret/key.txt
storage/tls/orchestrator.crt
storage/tls/orchestrator.key
```

These files are runtime storage and should not be committed.

## Environment

Main settings in `.env`:

```text
SECRET_KEY_FILE="storage/secret/key.txt"
ALLOWED_HOSTS="127.0.0.1,localhost,manager,orchestrator"

DB_HOST="localhost"
DB_PORT="3306"
DB_USER="root"
DB_PASS=""
DB_NAME="defly_manager"

SERVER_MANAGER="manager"
SERVER_USERNAME="defly-orchestrator"
SERVER_PASSWORD="P@55w0rd"

SERVER_PATH_PREFIX="api/v1"
SERVER_PATH_DEPLOYMENT="deployments"
SERVER_METHOD_DEPLOY="post"
SERVER_METHOD_FOLLOW="get"
SERVER_METHOD_CANCEL="delete"
SERVER_SOURCE_DEFENDER="./defender"
SERVER_DEFENDERS_TLS_VOLUME="defenders_tls"
SERVER_DOCKER_BASE_URL="tcp://localhost:2375"
```

`SERVER_MANAGER` is used by middleware to allow requests only from Manager. In
local development, set it to `localhost` if Manager is not running as the
`manager` Docker host.

`SERVER_SOURCE_DEFENDER` is resolved from the repository root. The default
`./defender` points to the sibling Defender project.

`SERVER_DEFENDERS_TLS_VOLUME` is the Docker volume key used for Defender TLS
files. When Orchestrator runs inside Docker Compose, the key is resolved with
the current Compose project name, so the default `defenders_tls` becomes a
volume like `defly_defenders_tls`.

## Run

From the `orchestrator` directory, run without TLS over HTTP:

```powershell
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

If running from the repository root, use `--project` and `--app-dir`:

```powershell
uv --project .\orchestrator run uvicorn configs.asgi:application --app-dir .\orchestrator --reload --host 0.0.0.0 --port 8000
```

To run with TLS over HTTPS, generate the TLS files first and pass the
certificate and key to Uvicorn:

```powershell
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000 --ssl-certfile ".\storage\tls\orchestrator.crt" --ssl-keyfile ".\storage\tls\orchestrator.key"
```

From the repository root:

```powershell
uv --project .\orchestrator run uvicorn configs.asgi:application --app-dir .\orchestrator --reload --host 0.0.0.0 --port 8000 --ssl-certfile ".\orchestrator\storage\tls\orchestrator.crt" --ssl-keyfile ".\orchestrator\storage\tls\orchestrator.key"
```

## Docker

Build the Orchestrator image from the `orchestrator` directory:

```powershell
docker build -t defly-orchestrator .
```

When running with Docker Compose, mount persistent storage and the Docker socket
so Orchestrator can create Defender containers:

```yaml
services:
  orchestrator:
    image: defly-orchestrator
    volumes:
      - orchestrator_storage:/app/storage
      - /var/run/docker.sock:/var/run/docker.sock
      - ../defender:/defender:ro
    environment:
      SERVER_SOURCE_DEFENDER: /defender
      SERVER_DEFENDERS_TLS_VOLUME: defenders_tls

volumes:
  orchestrator_storage:
  defenders_tls:
```

The Docker image starts Orchestrator with TLS by default. On first startup, the
entrypoint creates `storage/secret/key.txt` and the local TLS pair under
`storage/tls` when they do not already exist.

## API

Base path:

```text
/{SERVER_PATH_PREFIX}/{SERVER_PATH_DEPLOYMENT}/{defender_id}
```

Default route:

```text
/api/v1/deployments/{defender_id}
```

Default methods:

```text
POST   deploy defender
GET    follow defender logs
DELETE cancel defender
```

All requests require Basic Authentication using `SERVER_USERNAME` and
`SERVER_PASSWORD`.

HTTP example:

```powershell
curl.exe -u "defly-orchestrator:P@55w0rd" -X GET "http://localhost:8000/api/v1/deployments/<defender-id>"
```

HTTPS example with a self-signed local certificate:

```powershell
curl.exe -k -u "defly-orchestrator:P@55w0rd" -X GET "https://localhost:8000/api/v1/deployments/<defender-id>"
```

## Defender Deployment Storage

When deploying a Defender, Orchestrator:

- builds the Defender Docker image from `SERVER_SOURCE_DEFENDER`
- injects `DEFENDER_NAME` from the Manager defender name
- joins Defender to the same Docker Compose network as Orchestrator when
  Orchestrator is running inside Docker Compose
- mounts the Defender errors volume to `/app/storage/errors`
- mounts the Defender logs volume to `/app/storage/logs`
- mounts the shared Defender TLS volume to `/app/storage/tls`

The errors and logs volumes must already exist before deployment. They use the
normalized Defender container name plus `_errors` or `_logs`. For a Defender
named `edge-01`, define the Compose volumes as `edge-01_errors` and
`edge-01_logs`.

The shared `defenders_tls` volume is also created by Docker Compose and does not
include the Defender name. Manager should mount that same volume to its Defender
TLS storage directory, for example:

```yaml
services:
  manager:
    volumes:
      - defenders_tls:/var/www/html/storage/tls/defenders

  orchestrator:
    environment:
      SERVER_DEFENDERS_TLS_VOLUME: defenders_tls

volumes:
  edge-01_errors:
  edge-01_logs:
  defenders_tls:
```

Defender TLS files are written inside the shared TLS volume as
`{DEFENDER_NAME}.crt` and `{DEFENDER_NAME}.key`.

For local development outside Docker Compose, Orchestrator uses the raw volume
names without a Compose project prefix. Create them manually before deployment,
for example:

```powershell
docker volume create edge-01_errors
docker volume create edge-01_logs
docker volume create defenders_tls
```

In local mode, Orchestrator cannot infer a Compose network from its own
container, so Docker will use the default network behavior unless you run the
whole stack with Docker Compose.

## Checks

```powershell
uv run python manage.py check
uv run python -m ruff check .
```
