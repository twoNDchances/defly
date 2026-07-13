# User

A User is a human account that signs in to Defly Manager, owns resources, and acts as an authorization subject. A User can receive [Permissions](Permission.md) directly or indirectly through [Groups](Group.md).

## Configuration Fields

| Field | Required | Constraint and Meaning |
| --- | --- | --- |
| `name` | Yes | String up to 255 characters. |
| `email` | Yes | Valid, unique email up to 255 characters. |
| `password` | On create | 4 to 255 characters; hashed before storage. |
| `is_activated` | Yes | Enables or disables the account; default `true`. |
| `is_root` | Conditional | Highest administration privilege; visible/editable only by a current root account. |
| `is_verified` | On create | Marks email as verified; default `true`, editable only during creation. |

Leaving password blank while editing preserves the current hash. Password, remember token, and verification token are hidden from returned data.

## Manager Sign-in Conditions

A User can access `defly-manager` only when both are true:

```text
is_verified = true
AND is_activated = true
```

`is_verified` represents the email workflow; `is_activated` is an administrative switch. A verified but disabled User cannot sign in and receives no authorization from the [`Security` class](Permission.md#how-security-evaluates-permissions).

## Email Verification

When creating a User:

- With `is_verified = false`, Manager generates a UUID `verification_token` and queues a verification email.
- With `is_verified = true`, Manager calls `markEmailAsVerified()` and stores `email_verified_at`.
- A queueing failure is logged, but the User remains created.

The `is_verified` form field appears only during creation and is not a general edit switch.

## Root User

A root User bypasses permission checks when the authorization subject is that User. Root still requires `is_verified` and `is_activated`.

Only a current root User can create or change `is_root`. Queries made by non-root Users also exclude root accounts to prevent unauthorized visibility or modification.

When an API [Key](Key.md) has `is_reused = false`, the authorization subject is the Key rather than its User. The owner's root privilege is not inherited by that Key.

## Direct and Group Permissions

A User has two permission sources:

1. `users_permissions`: directly attached permissions.
2. `users_groups` -> `groups_permissions`: permissions from every Group the User belongs to.

One source matching `applied_for + action` is enough. `all` covers every action on the corresponding model. There is no deny permission; sources combine with OR.

See the complete algorithm in [Permission](Permission.md#how-security-evaluates-permissions).

## Ownership

Administrative models store `created_by` pointing to the User that created them. User owns Group, Permission, Guard, Label, Wordlist, Engine, Target, Action, Rule, Principle, Decision, Defender, Key, and Timeline records.

Ownership and authorization are different:

- `created_by` records who created/owns a record for traceability.
- Permission decides which action a User may perform.
- Policies and queries may impose record, lock, or workflow restrictions beyond Permission.

## Timeline

Creating, updating, or deleting a User over HTTP is recorded in [Timeline](Timeline.md) when a User context exists. Timeline stores the executor User, IP, method, path, action, and resource ID.

## Administration Checklist

- Use unique emails and check the mail queue when creating unverified Users.
- Disable with `is_activated` instead of deleting when audit history must remain.
- Grant root only to top-level administrators.
- Prefer Groups for shared access and direct Permissions for small exceptions.
- Check API Keys separately because a Key may not reuse its User's permissions.
