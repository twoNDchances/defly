# Permission

A Permission is a **model + action** authorization. It can attach directly to a [User](User.md) or [Key](Key.md), or indirectly through a [Group](Group.md).

## Configuration Fields

| Field | Required | Constraint |
| --- | --- | --- |
| `name` | Yes | Unique name up to 255 characters; kebab-case is not required. |
| `applied_for` | Yes | Model name with a Manager policy class, such as `Rule`, `Principle`, or `Defender`. |
| `action` | Yes | Action supported by that model's policy class. |
| `description` | No | Description of the permission scope. |

Changing `applied_for` clears the selected action and reloads valid actions. Validation rejects nonexistent model/action pairs.

## Action Catalog

Depending on the model policy, actions may include:

| Action | Meaning |
| --- | --- |
| `all` | All actions on the model. |
| `viewAny`, `view` | List and view one record. |
| `create`, `update` | Create and update. |
| `deleteAny`, `delete` | Bulk delete and delete one record. |
| `clone` | Clone a record. |
| `validate`, `validateAny` | Validate one or multiple Principles. |
| `deploy`, `deployAny` | Deploy Defenders. |
| `cancel`, `cancelAny` | Cancel Defender deployments. |
| `follow` | Follow deployment logs. |
| `refresh` | Refresh a Defender communication response. |
| `apply`, `applyAny` | Apply Principles to Defenders. |
| `revoke`, `revokeAny` | Revoke Principles. |
| `implement`, `implementAny` | Implement Decisions. |
| `suspend`, `suspendAny` | Suspend Decisions. |
| `review`, `reviewAny` | Mark Reports reviewed. |

The actual list is generated from public policy methods. Not every model supports every action.

System exclusions:

- `Pattern`: no `create`, `update`, `deleteAny`, or `delete`.
- `Report`: no `create` or `update`.
- `Timeline`: no `create` or `update`.

## How `Security` Evaluates Permissions

First, the User must exist with `is_verified = true` and `is_activated = true`. The system then selects the Permission subject:

1. If an API request has a Key with `key.is_reused = false`, the Key is the subject.
2. Otherwise, the User is the subject.

Then:

```text
if subject is User and user.is_root:
    allow
else if subject has Model:all directly or through a group:
    allow
else if subject has Model:action directly or through a group:
    allow
else:
    deny
```

Direct and Group Permissions combine with OR. There is no deny rule or priority order.

For guarded Defender operations, this Permission result is only the first gate. [Guard](Guard.md) then checks whether the current/requester User may operate that specific Defender.

## Permission Is Not the Only Condition

Having a Permission does not guarantee the operation will run. Authorization also checks record state:

- `is_locked = true` blocks `update`, `delete`, and `validate`.
- A `pending`/`processing` Defender blocks update/delete/deploy.
- A successfully deployed Defender cannot be deleted before cancellation.
- A Defender attached to one or more [Guards](Guard.md) requires the current/requester User to own the Defender or belong to an unexpired matching Guard.
- A `pending`/`validating` Principle blocks update/delete/validation.
- Report API verifies that the Report belongs to the Defender in the URL.

Permission answers whether the subject has an action class; policy and business rules answer whether it can act on the current record.

## Relationships

Permission has many-to-many relationships with User, Key, and Group and may carry [Labels](Label.md). `created_by` records the creating User.

## Example

```text
name: deploy-defender
applied_for: Defender
action: deploy
```

This allows deployment on an eligible Defender but does not grant `view`, `cancel`, or `deployAny`.

`Defender:all` covers Defender actions but still does not bypass deployment-state constraints or Guard membership unless the User also owns that Defender.

## Checklist

- Name the model and action accurately.
- Use `all` only when the subject needs complete model access.
- Inspect direct Permissions and Groups during an authorization investigation.
- For APIs, identify Key or User subject through `is_reused`.
- Do not confuse Permission with ownership, Guard membership, lock state, or workflow state.
