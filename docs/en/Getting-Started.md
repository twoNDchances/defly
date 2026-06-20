# Getting Started

This page starts the entire system with Docker Compose. Read the [Overview](Overview.md) first to understand each service's role.

## Requirements

- Docker Engine or Docker Desktop with Compose V2.
- Ports `80` and `443` available, or different Manager ports configured in `.env`.
- Enough Docker host resources for MariaDB, Manager, Worker, and Orchestrator.

## Start the System

From the repository root:

```powershell
Copy-Item .env.example .env
docker compose build defender
docker compose up -d --build
docker compose ps
```

Build the Defender image first because [Orchestrator](Orchestrator-Guide.md) uses it to deploy dynamic containers.

Open Manager at:

```text
https://localhost/defly-manager
```

If `USER_PASSWORD=random`, read the bootstrap credentials:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

Your browser may warn about the self-signed certificate in a local environment.

## Check the Services

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

Continue only after Manager is reachable, Worker is running, and Orchestrator can connect to Docker.

## Create a Minimal Policy

Before using the UI, read [Target](CoreConcepts/Target.md) -> [Engine](CoreConcepts/Engine.md) -> [Rule](CoreConcepts/Rule.md) -> [Action](CoreConcepts/Action.md) -> [Principle](CoreConcepts/Principle.md) -> [Decision](CoreConcepts/Decision.md).

An experimental policy should log or report before it blocks traffic:

1. Select a Pattern or create a Target that reads a specific request field.
2. Create a Rule whose comparator matches the Target datatype.
3. Attach a `log` or `report` Action to the Rule.
4. Create a Principle in the same phase and add the Rule.
5. Validate the Principle until its status is `passed`.

## Create the First Defender

1. Create a [Defender](CoreConcepts/Defender.md) record with a unique name and unused proxy port.
2. Set the backend URL in the proxy environment group.
3. Apply the required Principles and implement the required Decisions.
4. Run the deployment action in Manager.
5. Monitor `deployment_status` and deployment logs.

## Test the Proxy

Send requests through the Defender proxy port rather than directly to the backend. Then verify:

- The backend received the request.
- Defender logs contain no errors.
- A [Report](CoreConcepts/Report.md) is created when the corresponding Action runs.
- Scores and Decisions match expectations.

If deployment fails, go to [Troubleshooting](Troubleshooting.md). For detailed settings, read [Installation](Installation.md) and [Configuration](Configuration.md).
