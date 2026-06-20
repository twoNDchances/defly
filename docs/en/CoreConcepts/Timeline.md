# Timeline

Timeline is the audit log for administrative operations in Defly Manager. Each record identifies who did what, from which request, and on which resource.

Timeline differs from [Report](Report.md): Report records firewall-detected HTTP events; Timeline records administration of Manager data.

## Fields

| Field | Meaning |
| --- | --- |
| `created_at` | Audit record creation time. |
| `created_by` | Executor User, when identified. |
| `ipv4` | Client IPv4 when the request IP is valid IPv4. |
| `ipv6` | Client IPv6 when the request IP is valid IPv6. |
| `method` | Lowercase HTTP method. |
| `path` | Request path without domain. |
| `action` | Business action. |
| `resource_type` | Resource Eloquent polymorphic class. |
| `resource_id` | Resource UUID/ID. |

One request IP populates either `ipv4` or `ipv6`. Without HTTP request context, request fields may be `null`.

## Displayed Actions

| Action | Common Source |
| --- | --- |
| `create` | Observer after resource creation. |
| `update` | Observer after update. |
| `delete` | Observer after deletion. |
| `clone` | Clone operation. |
| `validate` | Principle validation. |
| `deploy` | Queue Defender deployment. |
| `cancel` | Queue Defender cancellation. |
| `follow` | View Defender container logs. |
| `refresh` | Refresh Defender response. |
| `apply`, `revoke` | Manage runtime Principles. |
| `implement`, `suspend` | Manage runtime Decisions. |
| `review` | Mark Report reviewed. |

The UI also has bulk counterparts, but Timeline stores the actual action string recorded by `Logger`.

## Supported Resources

Timeline may point to:

- User, Group, Permission, Key, Label
- Wordlist, Pattern, Engine, Target
- Action, Rule, Principle, Decision
- Defender, Report

`resource()` is polymorphic. After a resource is deleted, Timeline retains `resource_type` and `resource_id`, but the open-resource button may have no valid destination.

## How Timeline Is Created

Observer trait `After` calls `Logger` when a model is created, updated, or deleted. Business actions such as deploy, validate, apply, or review call `Logger` directly.

`Logger` writes only when:

- Action is non-empty.
- Resource has a key and exists, or action is `delete`.
- Resource is not Timeline itself.
- Application is not running in CLI.

Timeline is created through `withoutEvents` so audit records do not recursively create audit records. Write errors are reported without breaking the primary operation.

`updated` ignores a just-created model to avoid both `create` and `update` for one creation cycle.

## Ownership and Permissions

Timeline uses Owner, so `created_by` relates to User. Permission supports only list/view/delete; no create/update because the system must create records.

Deleting Timeline loses audit evidence and should be restricted. It is not an absolutely immutable append-only log because the controller exposes deletion.

## Limitations

- Stores operation metadata, not field before/after values.
- Does not store request/response bodies or Action results.
- Queued Actions are logged when queued and do not prove final success. Read result state from resources such as `deployment_status` or `last_response_details`.
- CLI operations are skipped.

## Investigate History

1. Identify User, IP, method, and path.
2. Open `resource_type + resource_id` if it still exists.
3. For asynchronous Actions, inspect resource status/details.
4. Correlate consecutive Timeline records for attach/apply or deploy/follow/cancel flows.

## Operations Checklist

- Grant Timeline deletion only to audit administrators.
- Do not replace application/container logs with Timeline.
- Add a dedicated audit mechanism when immutable field diffs are required.
- Keep displayed timezones consistent across Timeline, Report, and Defender logs.
