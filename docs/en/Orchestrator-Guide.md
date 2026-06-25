# Orchestrator Guide

Orchestrator is the Django ASGI boundary between Manager/Worker and Docker. This page owns Docker deployment behavior. Endpoint shapes belong in [API Reference](API-Reference.md), and variables belong in [Environment Variables](Environment-Variables.md).

## Responsibilities

Orchestrator:

- authenticates the internal caller and resolves the executor;
- checks the requested Defender action before a Docker side effect;
- creates, replaces, follows, and removes Defender containers;
- assigns image, environment, port, network, volume, and Compose labels;
- records deployment state/details in the shared database.

It does not own database migrations, WAF semantics, or Manager lifecycle buttons.

## Deployment Lifecycle

Manager records intent and Worker performs the queued call. Orchestrator then:

1. authenticates Basic Auth and the allowed caller host;
2. resolves the executor email and required Defender permission;
3. locks/loads the Defender deployment state;
4. inspects its Docker Compose context when running in Compose;
5. constructs and starts/replaces/removes the container;
6. stores `successful` or `failed` with actionable details.

The state model is documented in [Defender](CoreConcepts/Defender.md). Queue ownership
and retry operations belong in [Operations](Operations.md).

## Compose Context

A dynamic Defender receives the current Compose project labels, network membership,
and a distinct service label. This lets Compose recognize it as part of the project
without confusing it with Orchestrator. Outside Compose, no project context can be
inferred and Docker's normal network behavior applies.

When a Defender cannot reach MariaDB or its backend, verify container network and DNS
connectivity before inspecting policy logic.

## Container Resources

- The image comes from the configured Defender image name.
- The proxy port comes from the Defender record and is published on the host.
- TLS uses the shared Defender TLS volume.
- Logs and errors use per-Defender persistent volumes.
- Environment values are assembled from system configuration and the Defender record.

Replacing a container must not be treated as deleting persistent data. Volume naming,
defaults, and configuration keys belong in [Environment Variables](Environment-Variables.md).

## Docker Security

Docker access is host-administrator-equivalent. Prefer a local Unix socket or a
properly protected endpoint; never expose an unauthenticated Docker TCP API. Limit the
Orchestrator container, credentials, and network according to
[Security](Security.md#docker-daemon).

## Authentication and Authorization

Transport authentication and caller allowlisting happen before handlers. Deployment
middleware maps the configured route method to `deploy`, `follow`, or `cancel`, then
checks that action on the Defender model for the resolved executor. Missing identity,
permission, or contract agreement fails before Docker is called.

Exact headers, methods, and status responses belong in
[API Reference](API-Reference.md#orchestrator-api). Cross-service credential mapping
belongs in [Configuration](Configuration.md#manager-and-orchestrator).

## Follow and Cancel

Follow returns container output for Manager. Cancel stops/removes the deployment and
updates its state. Both originate from Worker jobs; if Manager appears stuck, inspect
the queue before retrying the Docker action.

For diagnosis commands and symptom order, use [Troubleshooting](Troubleshooting.md).
