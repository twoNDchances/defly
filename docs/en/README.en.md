# Defly

Defly is a multi-service system for managing security policies, deploying
Defender runtimes, and applying web application protection at the edge.

## Architecture

- `manager`: Laravel/Filament application for users, permissions, targets,
  engines, rules, principles, decisions, defenders, reports, and timelines.
- `orchestrator`: Django ASGI service called by Manager to deploy, follow logs,
  and cancel Defender containers through Docker.
- `defender`: Go runtime that runs the Defender control API and WAF reverse
  proxy.
- `mariadb`: shared database used by Manager, Orchestrator, workers, and
  deployed Defenders.

## Docker Installation

Docker Compose is the recommended way to run the full stack.

### Requirements

- Docker Engine or Docker Desktop with Compose V2.
- Ports `80` and `443` free, unless you change `MANAGER_HTTP_PORT` and
  `MANAGER_HTTPS_PORT`.

### 1. Create the root environment file

From the repository root:

```powershell
Copy-Item .env.example .env
```

Review `.env` before starting the stack. At minimum, set stable values for:

```text
MARIADB_ROOT_PASSWORD
DB_PASSWORD
ORCHESTRATOR_PASSWORD
APP_URL
USER_EMAIL
```

If `USER_PASSWORD=random`, Manager creates a random first-login password in
`/var/www/html/credentials.txt` inside the Manager container.

### 2. Build the Defender image

Orchestrator deploys Defenders from the local image configured by
`SERVER_DEFENDER_IMAGE`. Build it explicitly because the Defender service is a
build-only Compose profile.

```powershell
docker compose build defender
```

### 3. Start the stack

```powershell
docker compose up -d --build
```

Check service status:

```powershell
docker compose ps
```

Follow logs if a service is still starting:

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

### 4. Open Manager

Default URL:

```text
https://localhost/defly-manager
```

Manager generates a local self-signed certificate on first start. Your browser
may ask you to accept it.

If the first user password was random, read it with:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

### 5. Useful Docker commands

Run Manager Artisan commands:

```powershell
docker compose exec manager php artisan migrate --force
docker compose exec manager php artisan db:seed --force
docker compose exec manager php artisan optimize
```

Restart the stack:

```powershell
docker compose restart
```

Stop containers without deleting data:

```powershell
docker compose down
```

Stop containers and delete named volumes:

```powershell
docker compose down -v
```

Use `down -v` only when you intentionally want to delete database and runtime
storage.

### 6. Defender deployment notes

When a Defender is deployed from Manager, Orchestrator:

- uses the image configured by `SERVER_DEFENDER_IMAGE`
- creates or reuses per-Defender error and log volumes
- uses the shared `SERVER_DEFENDER_TLS_VOLUME` volume for Defender TLS files
- attaches the Defender container to the same Compose network as Orchestrator
- exposes the Defender proxy port configured on the Defender record

Manager and Orchestrator must use the same database and matching Orchestrator
credentials. The provided `docker-compose.yml` wires these values from the root
`.env` file.

## Manual Installation

Manual installation is useful for development. You still need Docker if you want
Orchestrator to deploy Defender containers.

### Requirements

- MariaDB or MySQL.
- PHP `8.3+`, Composer `2`, and PHP extensions required by Manager:
  `bcmath`, `curl`, `exif`, `gd`, `intl`, `mbstring`, `pcntl`, `pdo_mysql`,
  and `zip`.
- Node.js and npm for Manager assets.
- Python `3.14+`, `uv`, and MySQL client/build libraries for Orchestrator.
- Go `1.26.1+` for Defender development.
- Docker Engine/Desktop if using Orchestrator deployments.

### 1. Prepare the database

Create a database and user for Defly:

```sql
CREATE DATABASE defly_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'defly'@'%' IDENTIFIED BY 'change_me_app';
GRANT ALL PRIVILEGES ON defly_manager.* TO 'defly'@'%';
FLUSH PRIVILEGES;
```

Use the same database credentials in Manager, Orchestrator, and Defender
configuration.

### 2. Install Manager

From the `manager` directory:

```powershell
cd manager
Copy-Item .env.example .env
composer install
npm install
```

Edit `manager/.env`:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=defly_manager
DB_USERNAME=defly
DB_PASSWORD=change_me_app

ORCHESTRATOR_BASE_URL=http://127.0.0.1:8000
ORCHESTRATOR_TLS_SKIP_VERIFY=true

USER_EMAIL=root@defly.local
USER_PASSWORD=random
```

Initialize the Laravel app:

```powershell
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm run build
```

If `USER_PASSWORD=random`, read the generated password:

```powershell
Get-Content credentials.txt
```

Run Manager:

```powershell
php artisan serve --host=127.0.0.1 --port=8080
```

Run the queue worker in another terminal:

```powershell
php artisan queue:work --tries=3
```

Manager URL in this manual setup:

```text
http://127.0.0.1:8080/defly-manager
```

### 3. Install Orchestrator

Orchestrator reads the Manager-created database tables. Run Manager migrations
before starting Orchestrator.

From the `orchestrator` directory:

```powershell
cd orchestrator
Copy-Item .env.example .env
uv sync
```

Edit `orchestrator/.env`:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=defly
DB_PASS=change_me_app
DB_NAME=defly_manager

SERVER_MANAGER=127.0.0.1,localhost
SERVER_USERNAME=defly-orchestrator
SERVER_PASSWORD=P@55w0rd
SERVER_DEFENDER_IMAGE=defly-defender:latest
SERVER_DEFENDER_TLS_VOLUME=defender_tls
SERVER_DOCKER_BASE_URL=tcp://localhost:2375
```

`SERVER_DOCKER_BASE_URL` must point to a Docker daemon reachable from the
Orchestrator process. On Docker Desktop, enable the TCP Docker API only for a
trusted local development machine. On Linux you can use
`unix:///var/run/docker.sock`.

Generate the local Django secret key file:

```powershell
uv run python manage.py generatesecretkeyfile
```

Run Orchestrator over HTTP for local development:

```powershell
$env:DJANGO_SETTINGS_MODULE = "configs.development"
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

To run Orchestrator over HTTPS, generate TLS files and pass them to Uvicorn:

```powershell
uv run python manage.py generatetlsfile
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000 --ssl-certfile ".\storage\tls\orchestrator.crt" --ssl-keyfile ".\storage\tls\orchestrator.key"
```

If you use HTTPS, update Manager's `ORCHESTRATOR_BASE_URL`,
`ORCHESTRATOR_TLS_SKIP_VERIFY`, and `ORCHESTRATOR_TLS_CERT_FILE` accordingly.

### 4. Link TLS files for manual setup

Docker Compose already mounts the TLS volumes for Manager. In a manual local
setup, create links so Manager can read certificates generated by Orchestrator
or a manually started Defender.

The repository keeps placeholder TLS directories with `.gitignore` files. On
Windows, create a `shared` junction inside those directories from the repository
root:

```powershell
New-Item -ItemType Directory -Force manager\storage\tls
New-Item -ItemType Directory -Force manager\storage\tls\orchestrator
New-Item -ItemType Directory -Force manager\storage\tls\defenders
New-Item -ItemType Directory -Force orchestrator\storage\tls
New-Item -ItemType Directory -Force defender\storage\tls

New-Item -ItemType Junction -Path manager\storage\tls\orchestrator\shared -Target (Resolve-Path orchestrator\storage\tls)
New-Item -ItemType Junction -Path manager\storage\tls\defenders\shared -Target (Resolve-Path defender\storage\tls)
```

Use this Manager TLS configuration with those junctions:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY=false
ORCHESTRATOR_TLS_CERT_FILE=storage/tls/orchestrator/shared/orchestrator.crt
DEFENDER_SERVER_TLS_SKIP_VERIFY=false
DEFENDER_SERVER_TLS_DIRECTORY=storage/tls/defenders/shared
```

If `manager/storage/tls/orchestrator/shared` or
`manager/storage/tls/defenders/shared` already exists, move or remove that link
before creating it again. A junction links directories; it does not link
individual files.

If you want to keep Manager's default TLS paths, use file symlinks for the
Orchestrator files instead:

On Windows, file symlinks may require Developer Mode or an elevated terminal.

```powershell
New-Item -ItemType Directory -Force manager\storage\tls\orchestrator
New-Item -ItemType SymbolicLink -Path manager\storage\tls\orchestrator\orchestrator.crt -Target (Resolve-Path orchestrator\storage\tls\orchestrator.crt)
New-Item -ItemType SymbolicLink -Path manager\storage\tls\orchestrator\orchestrator.key -Target (Resolve-Path orchestrator\storage\tls\orchestrator.key)
```

Manager only needs the `.crt` file to verify Orchestrator. Linking the `.key`
is optional and mainly keeps the local TLS pair together.

For a manually started Defender, link the Defender certificate files by Defender
name:

```powershell
New-Item -ItemType Directory -Force manager\storage\tls\defenders
New-Item -ItemType SymbolicLink -Path manager\storage\tls\defenders\local-defender.crt -Target (Resolve-Path defender\storage\tls\local-defender.crt)
New-Item -ItemType SymbolicLink -Path manager\storage\tls\defenders\local-defender.key -Target (Resolve-Path defender\storage\tls\local-defender.key)
```

Replace `local-defender` with the exact `DEFENDER_NAME`. Manager verifies a
Defender using `{DEFENDER_SERVER_TLS_DIRECTORY}/{DEFENDER_NAME}.crt`; the
`.key` link is optional for Manager.

On Linux or macOS, use directory symlinks:

```sh
mkdir -p manager/storage/tls/orchestrator manager/storage/tls/defenders orchestrator/storage/tls defender/storage/tls
ln -sfn "$(pwd)/orchestrator/storage/tls" manager/storage/tls/orchestrator/shared
ln -sfn "$(pwd)/defender/storage/tls" manager/storage/tls/defenders/shared
```

When Defenders are deployed by Orchestrator into Docker, their TLS files are
written inside the Docker named volume `defender_tls`. A host-running Manager
cannot read that Docker volume directly; either run the full stack with Docker
Compose or set `DEFENDER_SERVER_TLS_SKIP_VERIFY=true` for local development.

### 5. Build Defender for Orchestrator deployments

From the repository root:

```powershell
docker build -t defly-defender:latest ./defender
docker volume create defender_tls
```

The shared `defender_tls` volume must exist before Orchestrator deploys a
Defender. Orchestrator creates per-Defender error and log volumes as needed.

### 6. Run Defender manually

Normally, Defenders are created by Manager through Orchestrator. For standalone
Defender development, run it directly from the `defender` directory after the
database has been migrated and seeded by Manager.

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

Default Defender ports:

- `9947`: Defender control API
- `9948`: Defender proxy

### 7. Development checks

Manager:

```powershell
cd manager
php artisan test
```

Orchestrator:

```powershell
cd orchestrator
uv run python manage.py check
uv run python -m ruff check .
```

Defender:

```powershell
cd defender
go test ./...
```
