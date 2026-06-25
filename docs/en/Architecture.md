# Architecture

Defly separates administration, orchestration, and traffic processing into independent services. [Manager](Manager-Guide.md) does not control Docker directly, and [Defender](CoreConcepts/Defender.md) does not own the database schema.

## System Diagram

```text
Administrator
    |
    v
Manager UI/API ----> MariaDB
    |
    v
Laravel Queue ----> Worker ----Basic Auth----> Orchestrator ----> Docker API
                                                        |
                                                        v
Client -> Defender proxy ----> Backend server      Defender container
                  |
                  +---- read policies / write reports -> MariaDB
```

## Ownership

| Component | Owns |
| --- | --- |
| Manager | Schema, migrations, seed data, administration UI/API, and policies. |
| Worker | Background jobs created by Manager. |
| Orchestrator | Defender container lifecycle, networks, volumes, and port mappings. |
| Defender | HTTP transactions, score/level state, logs, and WAF enforcement. |
| MariaDB | Shared data storage, with its schema managed by Manager. |

## Policy Pipeline

At the architectural level, policy data moves in this order:

```text
Pattern/Wordlist -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

This is an execution/readability sequence, not a claim that every neighboring model
has a direct database relationship. Model meaning, compatibility, and persisted
relationships belong in [Core Concepts](CoreConcepts/README.md).

## HTTP Lifecycle

Defender evaluates three request phases, applies request-direction Decisions, proxies
allowed traffic to the backend, then evaluates three response phases and
response-direction Decisions before returning data. Exact phase extraction belongs in
[Target](CoreConcepts/Target.md#six-http-phases); runtime ordering belongs in the
[Defender Guide](Defender-Guide.md).

## Deployment Flow

Manager creates a background job. Worker calls Orchestrator with the executor identity,
Orchestrator authorizes the requested lifecycle action, and only then may it change
Docker state. Orchestrator creates or removes the Defender container and reports the
resulting status to Manager. Container labels, networks, volumes, ports, and cleanup
behavior are owned by [Orchestrator](Orchestrator-Guide.md#deployment-lifecycle).

## Database

Manager, Orchestrator, and Defender share a database, but they do not have equal ownership. Migrations run only from Manager. Orchestrator and Defender must remain compatible with the current schema.

## TLS and Trust Boundaries

Manager can verify TLS when calling Orchestrator and Defender's control API. Orchestrator is highly privileged because it can access the Docker daemon. Read [Security](Security.md) before exposing either the Docker API or control APIs beyond a trusted host.

## Queue

Deployment, cancellation, and log-following operations may take time, so they run through Worker. If the UI created a request but its status does not change, inspect Worker before Orchestrator; see [Troubleshooting](Troubleshooting.md#queue-jobs-do-not-run).
