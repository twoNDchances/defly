# Key

A Key is an API token owned by a [User](User.md). Defly Manager API requires both the User's Basic Authentication and a token from a Key owned by that User.

## Configuration Fields

| Field | Required | Constraint |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `token` | On create | Unique 16 to 255 characters; the form generates 64 random characters. |
| `expired_at` | No | Expiry timestamp; `null` means no expiry. |
| `is_reused` | Yes | Selects User permissions instead of Key-specific permissions; default `false`. |
| `description` | No | Purpose or consuming system. |

The token is hashed before storage and hidden from returned data. Manager cannot recover plaintext from the database. Leaving token blank while editing preserves its hash; entering a new token replaces the old one.

## API Authentication

Middleware performs these steps:

1. Find the User by Basic Auth email and verify the password hash.
2. Read the token from the configured location, default header `X-Token-Key`.
3. Consider only Keys whose `created_by` matches that User.
4. Consider only unexpired Keys: `expired_at IS NULL` or `expired_at > now()`.
5. Use `Hash::check` across eligible Keys to find a match.
6. Set the authenticated User context and request attribute `authenticated_key`.

The middleware rejects `HEAD` with `405 Method Not Allowed`.

Example:

```http
Authorization: Basic <base64(email:password)>
X-Token-Key: <plaintext-api-token>
Accept: application/json
```

Token name/location are configurable. With location `body`, the token is read from a body field of the configured name.

## `is_reused` and the Permission Subject

Interpret `is_reused` according to current source behavior:

| Value | Permission Subject | Sources Used |
| --- | --- | --- |
| `false` | Key | Direct Key Permissions and Key Groups. |
| `true` | User | Direct User Permissions and User Groups. |

With `false`, a Key can be more restricted than its owner. Even a root owner's Key cannot bypass Permission checks because the subject is `Key`.

With `true`, Permissions/Groups attached directly to the Key do not participate; the API uses User access as in a normal User context.

## Relationships

A Key may attach:

- [Permissions](Permission.md) through `keys_permissions`.
- [Groups](Group.md) through `keys_groups`.

Key is not part of the current [Label](Label.md) system. The `onlyOwner` query limits Keys by the signed-in User's `created_by`.

## Expiry and Revocation

`expired_at` is checked on every request. Revoke a Key by:

- Setting `expired_at` to the current/past time.
- Rotating the token.
- Deleting the Key.
- Removing Permissions/Groups when `is_reused = false`.

With `is_reused = true`, removing Key-specific access does not reduce API access; change User permissions or switch the Key to `false`.

## Operational Security

- Plaintext is available only when the token is created or rotated.
- Never put tokens in Timeline, logs, or descriptions.
- Use a separate Key per integration for independent revocation.
- Set expiry for temporary integrations.
- Keep `is_reused = false` for explicit least privilege.
- Rotate periodically and immediately after suspected disclosure.

## Checklist

- Store plaintext at creation in an appropriate secret manager.
- Attach Permissions/Groups to the correct subject for `is_reused`.
- Ensure the Basic Auth User matches Key `created_by`.
- Check `expired_at` timezone when investigating `401`.
