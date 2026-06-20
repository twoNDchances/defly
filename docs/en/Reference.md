# Quick Reference

Use this page for lookup. Full explanations are in the linked pages.

## Repository Structure

```text
defly/
  manager/          Laravel/Filament, schema, and UI/API
  orchestrator/     Django ASGI, deployment through Docker
  defender/         Go control API, proxy, and firewall
  docs/vi/          Vietnamese documentation
  docs/en/          English documentation
  architectures/    Architecture diagrams
  docker-compose.yml
  .env.example
```

## Default Ports

| Component | Port |
| --- | --- |
| Manager HTTP | `80` |
| Manager HTTPS | `443` |
| Manual Manager | `8080` |
| Local Orchestrator | `8000` |
| Defender control API | `9947` |
| Manual Defender proxy | `9948` |

A deployed proxy port comes from the [Defender](CoreConcepts/Defender.md) record.

## Default URLs

| API/UI | Path |
| --- | --- |
| Manager UI | `/defly-manager` |
| Manager API | `/api/v1` |
| Orchestrator deployment API | `/api/v1/deployments/{defender_id}` |
| Defender control API | `/api/v1/principles`, `/api/v1/decisions` |

## Docker

| Resource | Default Name |
| --- | --- |
| Compose project | `defly` |
| Infrastructure network | `defly_infrastructure` |
| Defender image | `defly-defender:latest` |
| Defender TLS volume | `defly_defender_tls` |

Actual names depend on `COMPOSE_PROJECT_NAME` and `SERVER_DEFENDER_TLS_VOLUME`.

## WAF Pipeline

```text
Wordlist/Pattern -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

Read the [core concepts index](CoreConcepts/README.md) in order.

## Phases

| Number | Phase |
| --- | --- |
| `1` | Full request |
| `2` | Request headers/query/metadata |
| `3` | Request body/files |
| `4` | Response headers/metadata |
| `5` | Response body |
| `6` | Full response |

See [Target](CoreConcepts/Target.md#valid-types-by-phase) for valid types.

## Status Values

Principle validation:

```text
pending | validating | failed | passed
```

Defender deployment:

```text
pending | processing | failed | successful
```

Defender runtime:

```text
normal | abnormal
```

## Documentation by Task

- Install the system: [Installation](Installation.md)
- Understand configuration: [Configuration](Configuration.md)
- Look up environment variables: [Environment Variables](Environment-Variables.md)
- Use the UI: [Manager Guide](Manager-Guide.md)
- Operate containers: [Orchestrator Guide](Orchestrator-Guide.md)
- Understand firewall execution: [Defender Guide](Defender-Guide.md)
- Call APIs: [API Reference](API-Reference.md)
- Resolve failures: [Troubleshooting](Troubleshooting.md)
