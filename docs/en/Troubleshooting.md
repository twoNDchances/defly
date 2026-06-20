# Troubleshooting

Diagnose along this path: [Manager](Manager-Guide.md) -> Worker -> [Orchestrator](Orchestrator-Guide.md) -> Docker -> [Defender](CoreConcepts/Defender.md) -> backend -> policy. Stop at the first failing layer.

## Manager Cannot Call Orchestrator

Check:

- `ORCHESTRATOR_BASE_URL` and DNS.
- Matching Basic Auth values on both sides.
- The caller is included in `SERVER_MANAGER`.
- Matching deploy/follow/cancel paths and methods.
- TLS certificates and `ORCHESTRATOR_TLS_SKIP_VERIFY`.
- Worker is running.

View logs:

```powershell
docker compose logs -f manager worker orchestrator
```

## Queue Jobs Do Not Run

Symptom: Manager accepted an operation but deployment status does not change.

```powershell
docker compose ps worker
docker compose logs -f worker
```

Check `QUEUE_CONNECTION`, queue tables, timeout/attempt settings, and database connectivity. Restart Worker after changing environment variables or job code.

## Orchestrator Cannot Access Docker

Check `SERVER_DOCKER_BASE_URL`, socket permissions, and Docker daemon reachability from Orchestrator's execution environment.

With Docker Desktop, TCP port `2375` must be enabled when using a TCP configuration. Use it only on a trusted development machine.

## Defender Image Not Found

```powershell
docker compose build defender
docker image inspect defly-defender:latest
```

The inspected image name must match `SERVER_DEFENDER_IMAGE`.

## Defender Is Not Part of the Compose Project

Inspect labels and networks:

```powershell
docker inspect <defender-container>
```

The container needs `com.docker.compose.project`, `com.docker.compose.service`, `com.docker.compose.config-hash`, and `${COMPOSE_PROJECT_NAME}_infrastructure`. Redeploy the Defender after upgrading to an Orchestrator version that discovers Compose context.

## Port Already in Use

If Manager cannot bind `80/443`, change `MANAGER_HTTP_PORT` or `MANAGER_HTTPS_PORT`.

If Defender deployment fails on a port conflict, change `proxy_port` on the [Defender](CoreConcepts/Defender.md) record and redeploy. Two Defenders cannot publish the same host port.

## Database Connection Failure

Compare all three naming schemes:

- Manager: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- Orchestrator: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- Defender: `DATABASE_HOST`, `DATABASE_PORT`, `DATABASE_NAME`, `DATABASE_USER`, `DATABASE_PASS`.

Run migrations from Manager. Do not create an independent schema from Orchestrator or Defender.

## TLS Verification Failure

Verify that the certificate exists, is unexpired, matches the hostname, and is readable by the caller. For Defender, the filename must match the name Manager uses for certificate lookup.

Skip verification only briefly to isolate a local trust issue, then fix the certificate instead of retaining an insecure setting.

## Defender Cannot Reach the Backend

Check in order:

1. `PROXY_BACKEND_URL`.
2. The backend listens on the expected address and port.
3. DNS inside the container.
4. Defender and backend share a network or valid route.
5. Proxy timeout/TLS behavior.
6. Policy does not deny or cancel the request.

## Request Is Blocked Unexpectedly

1. Inspect the [Report](CoreConcepts/Report.md) and Rule details.
2. Determine the Target value after the Engine chain.
3. Check comparator, inversion flag, and Wordlist.
4. Check every Rule in the [Principle](CoreConcepts/Principle.md).
5. Identify which Action changes the score.
6. Identify which [Decision](CoreConcepts/Decision.md) matches that score.
7. Use [Timeline](CoreConcepts/Timeline.md) to find the latest change.

Temporarily replace blocking with logging/reporting or revoke the Principle when traffic must be restored.

## No Report

A Report is created only when the `report` Action runs. Check Rule/Principle matching, whether the Action is after `allow`/`deny`, Defender's database connection, and report-write error logs.

## Storage Permission Errors

Check mounts, ownership, and write access for `storage/errors`, `storage/logs`, `storage/requests`, Wordlist files, and TLS files. Recreating a container does not repair permissions inside a persistent volume.
