# API Reference

Defly exposes APIs from [Manager](Manager-Guide.md), [Orchestrator](Orchestrator-Guide.md), and [Defender](CoreConcepts/Defender.md), each with a distinct purpose and authentication mechanism. Examples use default prefixes; see [Configuration](Configuration.md) if paths or methods have changed.

## Manager API

Default base path:

```text
/api/v1
```

### Authentication

Every request requires both:

1. HTTP Basic Auth with a [User](CoreConcepts/User.md) email and password.
2. A [Key](CoreConcepts/Key.md) belonging to that User, in the `X-Token-Key` header by default.

Example:

```powershell
$headers = @{
    Accept = "application/json"
    "X-Token-Key" = "<api-token>"
}

Invoke-RestMethod `
    -Uri "https://localhost/api/v1/me" `
    -Authentication Basic `
    -Credential (Get-Credential) `
    -Headers $headers
```

The Key must not be expired. Valid Basic Auth with a missing or invalid Key still returns `401`.

### Resource CRUD

Primary resources:

```text
users
groups
permissions
labels
wordlists
patterns
engines
targets
actions
rules
principles
decisions
defenders
timelines
```

Standard operations:

| Method | Path | Meaning |
| --- | --- | --- |
| `GET` | `/{resources}` | Paginated list. |
| `POST` | `/{resources}` | Create. |
| `GET` | `/{resources}/{id}` | View details. |
| `PUT` | `/{resources}/{id}` | Replace all required data. |
| `PATCH` | `/{resources}/{id}` | Partial update. |
| `DELETE` | `/{resources}/{id}` | Delete. |

`patterns` supports list/view only. `timelines` supports list/view/delete only.

Many resources expose `GET /{resources}/payload` for an example request matching the current contract.

### Relationships

Relationship endpoints use this pattern:

| Method | Path | Meaning |
| --- | --- | --- |
| `GET` | `/{resources}/{id}/{relation}` | List related records. |
| `POST` | `/{resources}/{id}/{relation}` | Attach IDs. |
| `DELETE` | `/{resources}/{id}/{relation}` | Detach IDs. |

Attach/detach requests commonly contain:

```json
{
  "ids": ["<uuid-1>", "<uuid-2>"]
}
```

Examples include User-Permission, Group-User, Target-Engine, Rule-Action, Principle-Rule, resource-Label, and Defender-Principle/Decision relationships.

### Policy and Defender Endpoints

| Method | Path | Meaning |
| --- | --- | --- |
| `POST` | `/principles/{id}/validate` | Validate a [Principle](CoreConcepts/Principle.md). |
| `POST` | `/defenders/{id}/deploy` | Queue deployment. |
| `POST` | `/defenders/{id}/follow` | Queue log following. |
| `POST` | `/defenders/{id}/cancel` | Queue cancellation. |
| `POST` | `/defenders/{d}/principles/{p}/apply` | Apply a Principle. |
| `POST` | `/defenders/{d}/principles/{p}/revoke` | Revoke a Principle. |
| `POST` | `/defenders/{d}/decisions/{x}/implement` | Implement a [Decision](CoreConcepts/Decision.md). |
| `POST` | `/defenders/{d}/decisions/{x}/suspend` | Suspend a Decision. |
| `GET` | `/defenders/{d}/reports` | List [Reports](CoreConcepts/Report.md). |

## Orchestrator API

Default base path:

```text
/api/v1/deployments/{defender_id}
```

The API uses Basic Auth. Manager's `ORCHESTRATOR_USERNAME`/`ORCHESTRATOR_PASSWORD` must match Orchestrator's `SERVER_USERNAME`/`SERVER_PASSWORD`.

| Default Method | Action | Typical Response |
| --- | --- | --- |
| `POST` | Deploy Defender | `200`, deployment details. |
| `GET` | Follow logs | `200`, latest log output. |
| `DELETE` | Cancel Defender | `200`, cancellation details. |

Methods can be changed by matching environment variables on both sides. Orchestrator also checks `SERVER_MANAGER` and reads the executor email from the configured header.

Common errors:

- `400`: invalid Defender environment variables.
- `401`/`403`: invalid authentication or caller.
- `404`: Defender, container, or logs not found.
- `409`: log following before deployment is `successful`.
- `500`: Docker or deployment failure.

## Defender Control API

Default base address:

```text
http://<defender-host>:9947/api/v1
```

This API synchronizes policy on a running Defender:

| Default Method | Path | Meaning |
| --- | --- | --- |
| `PUT` | `/principles` | Apply a Principle. |
| `DELETE` | `/principles` | Revoke a Principle. |
| `PUT` | `/decisions` | Implement a Decision. |
| `DELETE` | `/decisions` | Suspend a Decision. |

Defender server variables can change methods and paths. Authorization and executor information protect requests. This API is internal and should not be exposed to the Internet.

## Common Headers and Body

```text
Accept: application/json
Content-Type: application/json
Accept-Language: en
```

Manager API supports language selection with `Accept-Language`. Validation errors commonly return `422` with per-field errors. A deployment job may be accepted by Manager before Orchestrator finishes, so monitor `deployment_status` rather than relying only on the job-creation response.
