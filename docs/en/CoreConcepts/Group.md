# Group

A Group collects [Users](User.md) or [Keys](Key.md) under one set of [Permissions](Permission.md). It supports roles such as `security-admin`, `policy-editor`, or `report-reviewer` without attaching every permission to every subject.

## Configuration Fields

| Field | Required | Constraint |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `description` | No | Notes about the Group purpose or scope. |

A Group has a UUID, `created_by`, timestamps, and optional [Labels](Label.md).

## Relationships

| Relationship | Pivot Table | Meaning |
| --- | --- | --- |
| `users` | `users_groups` | Users inherit permissions from the Group. |
| `keys` | `keys_groups` | API Keys inherit permissions when the Key is the authorization subject. |
| `permissions` | `groups_permissions` | Permissions granted by the Group. |
| `labels` | `labels_resources` | Classification metadata. |

Groups cannot contain other Groups; every member belongs directly.

## Permission Merging

A User or Key may belong to multiple Groups. `Security` allows an action when:

- A matching direct Permission exists; or
- Any Group has a matching Permission; or
- A Permission grants `all` on the corresponding model.

There is no priority or deny permission. Adding a Group can only expand access and cannot revoke access inherited elsewhere.

Example:

```text
group policy-readers: Principle:viewAny, Principle:view
group policy-editors: Principle:update, Principle:validate
user in both groups: union of all four permissions
```

## Groups and API Keys

A Key with `is_reused = false` uses direct Key Permissions and **Key Groups**. The owner's User Groups are ignored.

A Key with `is_reused = true` uses its User as the subject; **User Groups** apply and Groups attached to the Key do not. See [Key](Key.md#is_reused-and-the-permission-subject).

## Group Design

Design Groups around stable responsibilities rather than individuals:

- `policy-readers`: view the firewall pipeline only.
- `policy-editors`: create, change, and validate policy.
- `defender-operators`: deploy, cancel, apply, revoke, implement, and suspend.
- `report-reviewers`: view and mark Reports reviewed.

Do not put every permission in one Group unless every member needs the same impact scope.

## Audit History

Creating, changing, or deleting a Group is recorded in [Timeline](Timeline.md). Membership and Permission changes should be treated as security changes even though Group itself has only two data fields.

## Checklist

- Name Groups after roles, not individuals.
- Check overlapping Groups before concluding that access was revoked.
- Distinguish User Groups from Key Groups.
- Use `all` very carefully.
