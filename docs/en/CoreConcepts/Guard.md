# Guard

A Guard restricts who may operate selected [Defenders](Defender.md). It is a second access layer after normal [Permissions](Permission.md): Permission answers whether a subject has the action class, while Guard answers whether the current User may operate this specific Defender.

Guard does not change WAF traffic handling. It protects administrative and control-plane actions such as Defender deployment, cancellation, log following, policy apply/revoke, and Decision implement/suspend.

## Configuration Fields

| Field | Required | Constraint and Meaning |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case name up to 255 characters. |
| `description` | No | Administrative context for why this Guard exists. |
| `expired_at` | No | Expiration time. `null` means the Guard does not expire. |

Guard also has a UUID, `created_by`, timestamps, and optional [Labels](Label.md).

## Relationships

| Relationship | Pivot Table | Meaning |
| --- | --- | --- |
| `users` | `guards_users` | Users allowed by this Guard. |
| `defenders` | `guards_defenders` | Defenders protected by this Guard. |
| `labels` | `labels_resources` | Classification metadata. |

Guard membership is User-based. [Groups](Group.md), [Keys](Key.md), and Permissions can grant the action, but they do not by themselves satisfy a Defender Guard.

## Enforcement Rule

For a Defender operation:

```text
if defender has no Guards:
    allow the operation to continue through normal permission and workflow checks
else if current/requester User owns the Defender through defenders.created_by:
    allow
else if current/requester User belongs to at least one unexpired Guard attached to the Defender:
    allow
else:
    deny
```

An unexpired Guard is one where `expired_at` is `null` or later than the current time. If multiple Guards protect the same Defender, matching any active Guard is enough.

The User recorded in `defenders.created_by` owns that Defender and is not restricted by Guard for that Defender. Root Users and `Defender:all` Permissions that are not the owner still must satisfy Guard membership when the Defender is guarded. This is intentional: Guard narrows high-impact Defender operations to an approved operator set without locking the creator out of their own Defender.

Guard also scopes Defender visibility. A Defender with no Guard is public to users who can list/view Defenders. A guarded Defender is visible only to its owner or to Users in at least one unexpired matching Guard.

## Where Guard Is Checked

Guard is checked across the control path:

- Manager UI and Manager API authorization for Defender lifecycle and policy-control actions.
- Manager queued jobs before they call Orchestrator or Defender control APIs.
- Orchestrator deployment endpoints using the executor User.
- Defender control API authorization before policy synchronization.

If a job was queued before Guard membership changed, the job re-checks the requester when it runs. Removing a User from an active Guard or expiring the Guard can therefore stop pending operations.

## Design Guidance

- Use a non-expiring Guard for stable ownership boundaries such as an application team.
- Use `expired_at` for temporary incident or release access.
- Attach only the Defenders that need the extra boundary. A Defender with no Guard uses ordinary Permission and workflow rules.
- Keep Manager, Orchestrator, and Defender clocks reasonably synchronized because all three may evaluate expiration.
- Do not use Guard as a substitute for Permission. Users still need the required Defender action Permission.

## Example

```text
guard production-edge-operators
users: alice@example.com, bob@example.com
defenders: checkout-prod, account-prod
expired_at: null
```

Alice and Bob can operate those Defenders if they also have the required Permissions. A third User with `Defender:deploy` but not in `production-edge-operators` cannot deploy `checkout-prod`.
