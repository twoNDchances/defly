# Orchestrator Guide

Orchestrator is Manager's internal Django ASGI service. It exists to collect work that should not run directly inside Laravel: Docker control and AI provider calls.

## Responsibilities

- Receive internal requests from Manager/Worker with Basic Auth.
- Read the executor email from the configured header.
- Authorize before deploying Defenders or returning AI assistant responses.
- Create, replace, follow logs for, and remove Defender containers.
- Call the AI provider for Manager's assistant page.

Orchestrator does not own migrations, Manager UI, or WAF execution.

## Deploying Defenders

Manager creates a job, Worker calls Orchestrator, and Orchestrator checks `deploy`, `follow`, or `cancel` permission on the Defender. If allowed, it reads the Defender record, normalizes `environment_variables`, uses the configured image, assigns port, network, volumes, Compose labels, and updates `deployment_status`/`deployment_details` in the shared database.

Dynamic Defenders use Orchestrator's Compose context so `docker compose down` can clean up the same project. If a Defender cannot reach MariaDB or the backend, check network/DNS before policy logic.

## AI Assistant

Manager stores conversations and attached resources in the database, then calls Orchestrator's assistant endpoint. Orchestrator checks `Conservation:chat`, checks view permission for attached resources, applies message/character limits, and calls the AI provider using `AI_*` settings.

Orchestrator only returns the assistant content. Manager still stores the conversation and renders the UI.

## Security and Configuration

Docker access is host-administrator-equivalent, so never expose an unauthenticated Docker TCP API. Paths, methods, Basic Auth, executor header, TLS, and AI variables must match between Manager and Orchestrator; see [Configuration](Configuration.md), [Environment Variables](Environment-Variables.md), and [API Reference](API-Reference.md#orchestrator-api).
