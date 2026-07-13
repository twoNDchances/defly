# Overview

Defly is a multi-service web application firewall. [Manager](Manager-Guide.md) owns data and UI, [Orchestrator](Orchestrator-Guide.md) handles internal tasks that need separate privileges such as Docker and AI, and [Defender](CoreConcepts/Defender.md) enforces policy on real traffic.

## What Defly Solves

Defly supports:

- Managing users, permissions, and API keys for the security platform.
- Building policies from HTTP data through protective actions.
- Deploying multiple WAF instances for multiple backends.
- Tracking reports, status, and change history.
- Running the full system with Docker Compose or developing each service independently.

If the policy structure is unfamiliar, read [Core Concepts](CoreConcepts/README.md).

## Components

| Service | Responsibility |
| --- | --- |
| [Manager](Manager-Guide.md) | Laravel/Filament UI and API for configuration, access control, policies, Defenders, and reports. |
| Worker | Processes Laravel queue jobs such as deployment, cancellation, and log following. |
| [Orchestrator](Orchestrator-Guide.md) | Internal Django service that deploys Defenders through Docker and calls the AI provider for the assistant. |
| [Defender](CoreConcepts/Defender.md) | Reverse proxy and WAF that enforces policies on HTTP requests and responses. |
| MariaDB | Shared database whose schema is owned by Manager; each service reads or writes according to its responsibility. |

[Defender](CoreConcepts/Defender.md) refers both to a configuration record in Manager and to the running Go process.

## Main Flows

### Configuration and Deployment

1. A [User](CoreConcepts/User.md) creates policies and a Defender record in Manager.
2. Manager stores the data in MariaDB.
3. Worker makes an authenticated internal call to Orchestrator.
4. Orchestrator creates a Defender container on the Docker host.
5. Defender reads its applied policies from the database.

### Traffic

1. A client sends a request to the Defender proxy port.
2. Defender processes the three request phases.
3. [Principles](CoreConcepts/Principle.md) evaluate rules and run actions.
4. Request-direction [Decisions](CoreConcepts/Decision.md) are evaluated against the score.
5. If allowed, the request is forwarded to the backend.
6. The response passes through the three response phases and response-direction Decisions.
7. Defender returns data to the client and may create a [Report](CoreConcepts/Report.md).

## What to Read Next

- To try the system: [Getting Started](Getting-Started.md).
- To understand the system: [Architecture](Architecture.md).
- To build policies: [Core Concepts](CoreConcepts/README.md).
- To deploy for real use: [Installation](Installation.md), [Configuration](Configuration.md), and [Security](Security.md).
