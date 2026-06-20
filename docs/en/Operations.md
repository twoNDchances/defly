# Operations

This page targets Docker Compose deployments. Read [Architecture](Architecture.md) and [Configuration](Configuration.md) before changing networks, volumes, or credentials.

## Start and Stop

```powershell
docker compose up -d --build
docker compose ps
```

Restart static services:

```powershell
docker compose restart manager worker orchestrator
```

Stop the project while preserving volumes:

```powershell
docker compose down
```

[Defenders](CoreConcepts/Defender.md) created by Orchestrator carry current project labels, so `docker compose down` can discover and stop them too.

Delete named volumes as well:

```powershell
docker compose down -v
```

The final command deletes the database and persistent storage. Run it only after backup and project-name confirmation.

## View Logs

```powershell
docker compose logs -f mariadb manager worker orchestrator
```

View a dynamic Defender through Manager's follow action or Docker:

```powershell
docker ps --filter "label=com.docker.compose.project=defly"
docker logs -f <defender-container>
```

Change the filter when using a different `COMPOSE_PROJECT_NAME`.

## Layered Health Checks

Check in this order to avoid a false diagnosis:

1. MariaDB is healthy and accepts connections.
2. Manager UI/API responds.
3. Worker consumes jobs.
4. Orchestrator can call Docker.
5. Defender deployment is `successful` and health is `normal`.
6. Defender proxy reaches the backend.
7. Policy produces expected logs/reports.

Do not debug Rules before the network and backend work.

## Backup

The database stores policies, users, deployment status, reports, and audit history. Persistent storage may contain Wordlists, TLS material, logs, errors, and raw requests.

Before upgrades or volume deletion:

1. Dump MariaDB.
2. Back up required volumes.
3. Record image tags and the current `.env`.
4. Test restoration in an isolated environment.

After restoration, Manager, Orchestrator, and Defender must all use the restored database and compatible schema/image versions.

## Upgrade

1. Read `.env.example` changes for all three services.
2. Back up database and storage.
3. Pull/build new images.
4. Run Manager migrations.
5. Start static services.
6. Rebuild the Defender image.
7. Redeploy Defenders in a controlled sequence.
8. Send smoke-test requests and inspect [Reports](CoreConcepts/Report.md).

```powershell
docker compose build defender
docker compose up -d --build
```

## Rotate Credentials

Rotate:

- Database passwords.
- Manager-Orchestrator Basic Auth.
- Manager API [Keys](CoreConcepts/Key.md).
- Administrator passwords.
- TLS certificates/private keys.

When changing credentials shared by two services, update both sides before restarting to reduce disruption.

## TLS

When replacing a certificate:

1. Preserve the filename expected by the caller.
2. Check the volume or bind path.
3. Verify certificate read access and private-key protection.
4. Restart the server service.
5. Confirm callers no longer require `skip_verify`.

## Scale Worker

```powershell
docker compose up -d --scale worker=3
```

Monitor queue length, database load, and overlapping deployment jobs. More Workers do not add resources to the Docker host.

## WAF Data Retention

[Reports](CoreConcepts/Report.md), logs, and raw requests can grow quickly and contain sensitive data. Define retention, restrict read access, and establish deletion procedures. Recreating containers does not clean persistent volumes.
