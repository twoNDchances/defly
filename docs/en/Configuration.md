# Configuration

This page explains how Defly configuration is divided and which values must agree between services. The exhaustive variable reference belongs in [Environment Variables](Environment-Variables.md).

## Configuration Sources

| Runtime | Source | Purpose |
| --- | --- | --- |
| Docker Compose stack | Root `.env` | Images, published ports, shared credentials, database, volumes, and values injected into containers |
| Manual Manager | `manager/.env` | Laravel, queue, mail, Manager API, and outbound service connections |
| Manual Orchestrator | `orchestrator/.env` | Django, Docker access, AI provider, and internal API |
| Defender | Defender record and deployment environment | Backend, control server, proxy behavior, logging, and scoring |

Do not maintain the same deployment through multiple sources. Compose deployments should treat the root `.env` and rendered `docker compose config` as authoritative; manual runs should use each service's local `.env`.

## Cross-service Contracts

### Database

Manager owns the schema and migrations. Manager, Orchestrator, and every Defender must resolve the same database server, database name, and compatible credentials. Run Manager migrations before starting code that expects a newer schema.

### Manager and Orchestrator

The caller and receiver must agree on:

- Basic Auth username and password
- deployment path segments
- assistant path segments
- HTTP methods for deploy, follow, and cancel
- HTTP method for chat
- executor-email header name
- TLS verification policy

A mismatch is a transport/configuration failure, not a policy failure. Variable names on each side are mapped in [Cross-service Mapping](Environment-Variables.md#cross-service-mapping).

### Orchestrator and Docker

Orchestrator needs privileged Docker access, the Defender image name, and the shared TLS volume key. In Compose it discovers the current project/network context from its own container and applies that context to dynamic Defenders. Protect Docker access as described in [Security](Security.md#docker-daemon).

### Orchestrator and AI Provider

Orchestrator calls the AI provider for Manager's assistant page, so `AI_API_KEY`, `AI_BASE_URL`, `AI_MODEL`, timeout, and message limits must be configured on the Orchestrator side. Manager only sends the conversation ID and executor email.

### Manager and Defender

Manager control jobs identify a Defender by name and verify its control-server TLS certificate when verification is enabled. Certificate naming and shared storage must therefore remain consistent across Manager, Orchestrator, and Defender.

## Environment Choices

Local evaluation may use self-signed TLS and development credentials on a trusted host. Production configuration is a separate security profile, not a collection of one-off variable changes. Apply the complete controls in [Security](Security.md#production-checklist).

## Applying a Change

1. Find the variable and constraints in [Environment Variables](Environment-Variables.md).
2. Identify every side of its cross-service contract.
3. Update the authoritative configuration source.
4. Inspect `docker compose config` for Compose deployments.
5. Restart only affected static services.
6. Redeploy Defenders when image, environment, network, volume, or published-port data changed.
7. Verify the relevant layer using [Operations](Operations.md#layered-health-checks).
