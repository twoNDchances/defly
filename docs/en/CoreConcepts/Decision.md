# Decision

A Decision is a verdict evaluated after [Principles](Principle.md). It compares the current violation score with a threshold and applies a final action in the `request` or `response` direction.

A Decision does not detect attacks. An [Action](Action.md) such as `suspect` or `score` must establish the score earlier in the same HTTP transaction.

## Execution Flow

```text
Principles and Actions
        |
        v
Current violation score
        |
        v
Direction + condition + threshold
        |
        v
Decision action
```

Defender runs only attached Decisions with `is_implemented = true`, using the `order` field from `defenders_decisions`.

## Common Fields

| Field | Required | Validation | Meaning |
| --- | --- | --- | --- |
| `name` | Yes | Unique, up to 255 characters, lowercase letters/numbers/`-` | Decision identifier. |
| `direction` | Yes | `request` or `response` | HTTP direction. |
| `condition` | Yes | `<=`, `<`, `=`, `>=`, `>` | Score comparison operator. |
| `score` | Yes | Numeric, at least `5`; UI uses integer and defaults to `5` | Comparison threshold. |
| `action` | Yes | Must be valid for direction | Action when condition is true. |
| `description` | No | Nullable | Purpose and scope. |
| `configurations` | Generated | Possibly empty JSON | Built by Manager from action-specific fields. |

With:

```text
condition = >=
score = 15
```

the Decision matches when current violation score is at least `15`.

## Direction and Timing

### Request

A `request` Decision runs after phases `1`, `2`, and `3`, before forwarding to the backend. It can allow, block, rewrite, or redirect the request.

### Response

A `response` Decision runs after phases `4`, `5`, and `6`, before returning data to the client. It can allow, block, or rewrite the response.

## Valid Actions by Direction

| Action | Request | Response | Configuration |
| --- | :---: | :---: | :---: |
| `allow` | Yes | Yes | No |
| `deny` | Yes | Yes | Yes |
| `rewrite_headers` | Yes | Yes | Yes |
| `rewrite_body` | Yes | Yes | Yes |
| `redirect` | Yes | No | Yes |
| `cancel` | Yes | No | No |
| `rewrite` | Yes | No | Yes |
| `save` | Yes | No | Yes |
| `erase_cookies` | No | Yes | No |
| `force_no_cache` | No | Yes | No |

Manager rejects invalid combinations, such as response `redirect`.

## Allow (`allow`)

No fields; Manager stores `configurations = null`.

Defender:

1. Marks the transaction allowed.
2. Stops remaining Decisions in the current direction.
3. Preserves changes from earlier Decisions.

Request `allow` forwards the request if not already denied/cancelled. Response `allow` preserves the current response and stops later response Decisions.

## Deny (`deny`)

### Manager and API Fields

| Field | Required | Value |
| --- | --- | --- |
| `deny_directive` | Yes | `use_default` or `copy_record` |
| `deny_record` | For `copy_record` | UUID of a `deny` [Action](Action.md) |

The Decision form does not directly accept status/content type/body. Create a `deny` Action and select `copy_record` for a custom response.

### Use Default

API input:

```json
{
  "action": "deny",
  "deny_directive": "use_default"
}
```

Stored JSON:

```json
{
  "directive": "use_default",
  "record": null,
  "status": null,
  "content_type": null,
  "body": null
}
```

Effective Defender defaults:

```text
status: 403
content type: application/json
body: {"message":"request denied"}
```

### Copy Record

API input:

```json
{
  "action": "deny",
  "deny_directive": "copy_record",
  "deny_record": "<deny-action-uuid>"
}
```

Manager verifies a `deny` Action and snapshots its configuration:

```json
{
  "directive": "copy_record",
  "record": "<deny-action-uuid>",
  "status": 403,
  "content_type": "json",
  "body": "{\"message\":\"Forbidden\"}"
}
```

The source Action requires:

- `status`: valid HTTP status.
- `content_type`: `json` or `html`.
- `body`: required string; valid JSON for JSON type.

The snapshot is copied when Decision is saved. Later Action changes do not update it; resave the Decision to refresh.

### Runtime Behavior

- Request deny: skip backend and return a blocking response.
- Response deny: replace status, clear old response headers, set content type, and replace body.
- Stop remaining Decisions in the current direction.

## Rewrite Headers

`rewrite_headers` affects request or response headers according to direction.

### Manager and API Fields

| Field | Required | Content |
| --- | --- | --- |
| `rewrite_headers_directive` | Yes | `set` or `unset` |
| `rewrite_headers_set` | For `set` | Array of `key`, `value` |
| `rewrite_headers_unset` | For `unset` | Array of `key` |

Keys are strings up to 255 characters. The UI allows letters, numbers, hyphens, underscores; values are strings.

Set input:

```json
{
  "action": "rewrite_headers",
  "rewrite_headers_directive": "set",
  "rewrite_headers_set": [
    {"key": "x-defly-decision", "value": "reviewed"}
  ]
}
```

Stored:

```json
{
  "directive": "set",
  "execution": [
    {"key": "x-defly-decision", "value": "reviewed"}
  ]
}
```

Unset stores:

```json
{
  "directive": "unset",
  "execution": [
    {"key": "x-debug-token"}
  ]
}
```

`set` adds or replaces all values for a header. `unset` removes it. Neither stops later Decisions.

## Rewrite Body

Manager designs `rewrite_body` to set or remove fields in the current direction's body.

### Manager and API Fields

| Field | Required | Content |
| --- | --- | --- |
| `rewrite_body_directive` | Yes | `set` or `unset` |
| `rewrite_body_set` | For `set` | Array of `key`, `value` |
| `rewrite_body_unset` | For `unset` | Array of `key` |

Input:

```json
{
  "action": "rewrite_body",
  "rewrite_body_directive": "set",
  "rewrite_body_set": [
    {"key": "security.status", "value": "blocked"}
  ]
}
```

Stored:

```json
{
  "directive": "set",
  "execution": [
    {"key": "security.status", "value": "blocked"}
  ]
}
```

### Current Runtime Limitation

Manager stores `directive + execution`, but Defender currently rewrites only when `configurations` contains a `body` or `value` string. It does not interpret Manager's key/value list.

Therefore Manager-created `rewrite_body` does not currently set/remove fields as the UI describes. Do not use it in production policy until Defender is synchronized and end-to-end tests cover JSON, forms, and response bodies.

## Redirect

`redirect` is request-only.

### Manager and API Fields

| Field | Required | Validation |
| --- | --- | --- |
| `redirect_url` | Yes | Valid URL |

Stored JSON:

```json
{
  "url": "https://alternative-backend.example/internal"
}
```

This is not an HTTP `3xx` redirect to the client. Defender replaces request URL and Host to forward to another backend, then stops all remaining request and response Decisions for the transaction.

## Cancel (`cancel`)

Request-only with no configuration; Manager stores `configurations = null`.

Defender marks cancellation, stops all Decisions, and attempts to hijack/close the client connection without a blocking response. Actual behavior depends on server/protocol support for connection hijacking.

## Rewrite Request

`rewrite` is request-only with type `path` or `query`.

### Rewrite Path

| Field | Required | Validation |
| --- | --- | --- |
| `rewrite_type` | Yes | `path` |
| `rewrite_path` | Yes | String beginning with `/` |

Input:

```json
{
  "action": "rewrite",
  "rewrite_type": "path",
  "rewrite_path": "/safe-path"
}
```

Stored:

```json
{
  "type": "path",
  "path": "/safe-path",
  "query": null
}
```

Defender replaces `request.URL.Path` and preserves the current query.

### Set Query Parameters

| Field | Required | Value |
| --- | --- | --- |
| `rewrite_type` | Yes | `query` |
| `rewrite_query_directive` | Yes | `set` |
| `rewrite_query_set` | Yes | Array of `key`, `value` |

Stored:

```json
{
  "type": "query",
  "path": null,
  "query": {
    "directive": "set",
    "execution": [
      {"key": "reviewed", "value": "1"}
    ]
  }
}
```

Defender adds new keys or replaces existing values.

### Unset Query Parameters

```json
{
  "type": "query",
  "path": null,
  "query": {
    "directive": "unset",
    "execution": [
      {"key": "debug"}
    ]
  }
}
```

Defender removes the query key. Request rewrites do not stop later Decisions.

## Save Request

`save` is request-only.

### Manager and API Fields

| Field | Required | Validation |
| --- | --- | --- |
| `save_position` | Yes | `prefix` or `suffix`; UI default `prefix` |
| `save_name` | Yes | String without `/`, `\`, `:`, `*`, `?`, `"`, `<`, `>`, or `|` |

Stored:

```json
{
  "position": "prefix",
  "name": "blocked-request"
}
```

Defender stores the raw request in `storage/requests`; Manager has no directory field.

Filename:

```text
prefix: <name>-<UTC timestamp>.http
suffix: <UTC timestamp>-<name>.http
```

Entering `request.json` still adds `.http`, for example `request.json-20260619-103000.000000000.http`.

Files use mode `0600`. Directory/write failures are logged and later Decisions continue.

## Erase Cookies (`erase_cookies`)

Response-only with no configuration.

Defender:

1. Removes backend `Set-Cookie` headers from the current response.
2. Reads cookies from the request.
3. Adds expired `Set-Cookie` with `Path=/`, `Max-Age=0` for each cookie name.

It does not stop later Decisions. Cookies with special Domain/Path may need additional deletion logic because runtime uses `/` and no Domain.

## Force No Cache

`force_no_cache` is response-only with no configuration.

Defender sets:

```http
Cache-Control: no-store, no-cache, must-revalidate, max-age=0
Pragma: no-cache
Expires: 0
```

It does not stop later Decisions.

## Ordering and Stops

| Action | Stops Current Direction | Stops Both Directions |
| --- | :---: | :---: |
| `allow` | Yes | No |
| `deny` | Yes | No |
| `redirect` | Yes | Yes |
| `cancel` | Yes | Yes |
| All others | No | No |

Order specific Decisions before general ones. An early `allow` can make a later `deny` unreachable.

## Attach and Implement on Defender

Attaching a Decision to [Defender](Defender.md) creates:

```text
order
is_implemented = false
```

To use it at runtime:

1. Defender deployment is `successful`.
2. Decision is attached.
3. Run implement.
4. Worker calls Defender control API.
5. On success, Manager sets `is_implemented = true`.

Suspend reverses the process and sets `is_implemented = false`. At startup, Defender loads only implemented Decisions and sorts them by pivot order.

## Complete Decision Examples

Deny requests scoring at least `15`, using a stored `deny` Action:

```json
{
  "name": "deny-high-risk-request",
  "direction": "request",
  "condition": ">=",
  "score": 15,
  "action": "deny",
  "deny_directive": "copy_record",
  "deny_record": "<deny-action-uuid>",
  "description": "Block requests whose accumulated score is high risk."
}
```

Add a response header at score `5` or higher:

```json
{
  "name": "mark-suspicious-response",
  "direction": "response",
  "condition": ">=",
  "score": 5,
  "action": "rewrite_headers",
  "rewrite_headers_directive": "set",
  "rewrite_headers_set": [
    {"key": "x-defly-risk", "value": "suspicious"}
  ]
}
```

## Checklist

- Direction supports the action.
- Score is at least `5` and condition has intended meaning.
- Custom deny references a valid `deny` Action.
- Set lists contain key/value; unset lists contain key.
- Rewrite path begins with `/`.
- Redirect URL is a valid backend URL, not confused with HTTP 3xx.
- Save name has no forbidden characters and storage is writable.
- Decision is attached, implemented, and correctly ordered on Defender.
- Do not use keyed `rewrite_body` in production before runtime synchronization.
