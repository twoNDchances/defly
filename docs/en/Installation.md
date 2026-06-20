# Installation

Defly can run in two ways:

- Docker Compose for the full system, suitable for evaluation and single-host operation.
- Manual installation of each service, suitable for development.

Read [Architecture](Architecture.md) to understand why Manager, Worker, Orchestrator, and Defender share certain resources.

## Docker Compose

### Requirements

- Docker Engine or Docker Desktop with Compose V2.
- Unused Manager and Defender proxy ports.
- A Docker daemon that allows Orchestrator to create dynamic containers.

### 1. Create the Configuration

```powershell
Copy-Item .env.example .env
```

At minimum, change these values before starting:

```text
MARIADB_ROOT_PASSWORD
DB_PASSWORD
ORCHESTRATOR_PASSWORD
APP_URL
USER_EMAIL
USER_PASSWORD
```

See [Configuration](Configuration.md#docker-compose-configuration) for complete descriptions.

### 2. Build the Defender Image

[Orchestrator](Orchestrator-Guide.md) deploys Defender from `SERVER_DEFENDER_IMAGE`:

```powershell
docker compose build defender
```

The image name must match `.env`; the default is `defly-defender:latest`.

### 3. Start the System

```powershell
docker compose up -d --build
docker compose ps
```

Follow startup logs:

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

### 4. Sign In to Manager

Default address:

```text
https://localhost/defly-manager
```

If `USER_PASSWORD=random`:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

Manager may use a self-signed certificate in a local environment, so the browser will ask for confirmation on first access.

### 5. Check Compose Resources

The system creates this network:

```text
${COMPOSE_PROJECT_NAME}_infrastructure
```

Static services and Defenders deployed by Orchestrator all join this network. Dynamic Defender containers receive the current project's Compose labels, allowing `docker compose down` to discover and stop them with the project.

Database, storage, TLS, log, and error volumes outlive containers. Do not use `docker compose down -v` unless you intend to delete their data.

### 6. Create a Defender

After signing in, follow [Getting Started](Getting-Started.md#create-the-first-defender). The Defender configuration model is described in [Defender](CoreConcepts/Defender.md).

## Manual Installation

This approach runs services from source while still allowing Docker for MariaDB and Defender containers.

### Development Requirements

- PHP `8.3+`, Composer `2`, and the required Laravel extensions.
- Node.js and npm for Manager assets.
- Python `3.14+` and `uv` for Orchestrator.
- Go `1.26.1+` for Defender.
- MariaDB or MySQL.
- Docker if Orchestrator must deploy Defenders.

### 1. Database

Create a shared database:

```sql
CREATE DATABASE defly_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'defly'@'%' IDENTIFIED BY 'change_me_app';
GRANT ALL PRIVILEGES ON defly_manager.* TO 'defly'@'%';
FLUSH PRIVILEGES;
```

Manager owns migrations. Always run Manager migrations before starting Orchestrator or Defender.

### 2. Manager and Worker

```powershell
cd manager
Copy-Item .env.example .env
composer install
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```

Configure the database and Orchestrator in `manager/.env`, then run:

```powershell
php artisan serve --host=127.0.0.1 --port=8080
```

In another terminal:

```powershell
cd manager
php artisan queue:work --tries=3
```

The local Manager address is `http://127.0.0.1:8080/defly-manager`.

### 3. Orchestrator

```powershell
cd orchestrator
Copy-Item .env.example .env
uv sync
uv run python manage.py generatesecretkeyfile
$env:DJANGO_SETTINGS_MODULE = "configs.development"
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

`SERVER_DOCKER_BASE_URL` must point to a Docker daemon reachable by Orchestrator. Linux can use `unix:///var/run/docker.sock`; TCP port `2375` should be used only on a trusted development host.

### 4. Defender

Orchestrator normally creates Defenders. To run one directly:

```powershell
cd defender
go mod download

$env:DATABASE_HOST = "127.0.0.1"
$env:DATABASE_PORT = "3306"
$env:DATABASE_NAME = "defly_manager"
$env:DATABASE_USER = "defly"
$env:DATABASE_PASS = "change_me_app"
$env:DEFENDER_NAME = "local-defender"
$env:PROXY_BACKEND_URL = "http://127.0.0.1:8080"

go run ./cmd/defender
```

Default ports are `9947` for the control API and `9948` for the proxy.

### 5. TLS for Manual Execution

Manager must be able to read Orchestrator and Defender certificates unless verification is skipped. In a local environment, the simplest approach is shared storage paths or directory links.

Related variables:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY
ORCHESTRATOR_TLS_CERT_FILE
DEFENDER_SERVER_TLS_SKIP_VERIFY
DEFENDER_SERVER_TLS_DIRECTORY
```

Do not link private keys into locations that do not need them. Manager needs only the `.crt` certificate to verify the peer. See [Security](Security.md#tls-between-services).

## Post-installation Verification

1. Manager is reachable and accepts sign-in.
2. Worker consumes jobs.
3. Orchestrator can connect to Docker.
4. The Defender image exists.
5. Defender can connect to the database and backend.
6. Requests through the proxy produce the expected WAF result.

If any step fails, see [Troubleshooting](Troubleshooting.md).
