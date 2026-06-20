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

Policies follow this order:

```text
Pattern/Wordlist -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

- [Pattern](CoreConcepts/Pattern.md) and [Wordlist](CoreConcepts/Wordlist.md) provide reusable data.
- [Target](CoreConcepts/Target.md) selects HTTP data.
- [Engine](CoreConcepts/Engine.md) transforms values.
- [Rule](CoreConcepts/Rule.md) compares values.
- [Action](CoreConcepts/Action.md) updates the HTTP transaction or creates a side effect.
- [Principle](CoreConcepts/Principle.md) combines rules with AND and coordinates their actions.
- [Decision](CoreConcepts/Decision.md) renders a verdict from the score.

## HTTP Lifecycle

Defender captures the request before running each phase:

1. Full request.
2. Request headers, query parameters, and metadata.
3. Request body and files.

After the request Principles, request-direction Decisions run. Unless the transaction reaches `deny` or `cancel`, the request is forwarded to the backend.

When the backend returns data, Defender runs:

4. Response headers and metadata.
5. Response body.
6. Full response.

Finally, response-direction Decisions are applied before data is returned to the client. See [Target](CoreConcepts/Target.md#six-http-phases) for phase and type details.

## Deployment Flow

Manager creates a background job. Worker calls Orchestrator with credentials and executor information. Orchestrator validates the request, uses the Docker API to create a container, attaches the `defly_infrastructure` network and TLS/log/error volumes, maps the port, and updates deployment status.

Dynamic containers receive Compose project and configuration labels so the project's `docker compose down` command can discover and stop them with the rest of the system.

## Database

Manager, Orchestrator, and Defender share a database, but they do not have equal ownership. Migrations run only from Manager. Orchestrator and Defender must remain compatible with the current schema.

## TLS and Trust Boundaries

Manager can verify TLS when calling Orchestrator and Defender's control API. Orchestrator is highly privileged because it can access the Docker daemon. Read [Security](Security.md) before exposing either the Docker API or control APIs beyond a trusted host.

## Queue

Deployment, cancellation, and log-following operations may take time, so they run through Worker. If the UI created a request but its status does not change, inspect Worker before Orchestrator; see [Troubleshooting](Troubleshooting.md#queue-jobs-do-not-run).
