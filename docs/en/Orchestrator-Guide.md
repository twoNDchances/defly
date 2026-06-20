# Orchestrator Guide

Orchestrator is a Django service that receives requests from Manager/Worker and controls Docker to deploy [Defenders](CoreConcepts/Defender.md).

## Responsibilities

- Authenticate Basic Auth credentials and the calling host.
- Read the Defender record and deployment configuration.
- Create, replace, or remove containers.
- Attach networks, volumes, ports, and environment variables.
- Read container logs.
- Update status and errors in the database.

Orchestrator does not create migrations or decide WAF policy.

## Deployment Lifecycle

Typical states:

```text
pending -> processing -> successful
                      -> failed
```

Flow:

1. Manager creates a job.
2. Worker calls the deployment endpoint.
3. Orchestrator authenticates the caller and loads the Defender.
4. The Docker service discovers the current Compose context from the Orchestrator container.
5. It creates the Defender with its image, environment, volumes, networks, and Compose labels.
6. It stores the final status and details.

## Docker Compose Discovery

A dynamic Defender inherits important Compose labels such as project, service, configuration hash, working directory, and configuration file. Docker Compose can therefore recognize it as a member of the current project and stop it during `docker compose down`.

The Defender receives a distinct service label to avoid colliding with Orchestrator while retaining the same project label.

## Networks

Orchestrator reads the current container's networks and joins the Defender to them. The default primary network is:

```text
${COMPOSE_PROJECT_NAME}_infrastructure
```

If a Defender cannot reach the database or backend, check network connectivity before investigating WAF policy.

## Volumes

- TLS uses the shared volume from `SERVER_DEFENDER_TLS_VOLUME`.
- Logs and errors use per-Defender volumes.
- Compose volume names may be prefixed by `COMPOSE_PROJECT_NAME`.

Do not delete volumes merely to redeploy a container.

## Docker API

`SERVER_DOCKER_BASE_URL` may use TCP or a Unix socket:

```text
tcp://localhost:2375
unix:///var/run/docker.sock
```

Docker access is effectively host-administrator access. Never expose the TCP Docker API to an untrusted network; see [Security](Security.md#docker-daemon).

## API Authentication

Manager uses `ORCHESTRATOR_USERNAME` and `ORCHESTRATOR_PASSWORD`; Orchestrator uses `SERVER_USERNAME` and `SERVER_PASSWORD`. The pairs must match.

`SERVER_MANAGER` limits callers. `SERVER_EMAIL_HEADER_KEY` names the header carrying the executor email for audit history.

## Follow Logs and Cancel

Following logs reads container output for display in Manager. Cancellation stops/removes the corresponding Defender and updates deployment status. Both operations run through Worker, so inspect the queue when the UI does not update.

## When Deployment Fails

Check in this order:

1. Can Worker call Orchestrator?
2. Are credentials and allowed callers correct?
3. Can Orchestrator access Docker?
4. Does `SERVER_DEFENDER_IMAGE` exist?
5. Is the proxy port already used?
6. Do networks and volumes exist with suitable permissions?
7. Can Defender reach the database and backend?

See [Troubleshooting](Troubleshooting.md) for commands.
