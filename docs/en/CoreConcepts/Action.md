# Action

An Action describes what Defender does after a [Rule](Rule.md) in a [Principle](Principle.md) matches. It can allow/deny an HTTP transaction, log, send an HTTP request, create a [Report](Report.md), or change score, level, or runtime variables.

Action differs from [Decision](Decision.md): Actions run inside Principles and may alter transaction state; Decisions run after the Principles for a request/response direction and use the total score for a verdict.

## Common Fields

| Field | Required | Meaning |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `type` | Yes | One of nine Action types; form default `allow`. |
| Type-specific fields | By type | Packed by Manager into JSON `configurations`. |
| `description` | No | Administration notes. |

## Ordering and Chain Stops

Actions attach to Rule through a pivot with `order`; Defender follows that order.

- Before each Action, Defender checks whether the transaction is already allowed or denied.
- `allow` and `deny` set a terminal flag; later Actions do not run.
- Other Actions continue the chain.
- `request` and `report` run asynchronously in goroutines. Defender starts them and continues without waiting.
- Invalid Actions are recorded in validation logs; depending on the error, a built object may still execute.

## Configuration Matrix

| Type | Configuration | Synchronous | Stops Chain |
| --- | --- | --- | --- |
| `allow` | None | Yes | Yes |
| `deny` | `status`, `content_type`, `body` | Yes | Yes |
| `log` | `format`, `console`, `file` | Yes | No |
| `request` | `url`, `method`, `headers`, `body` | No | No |
| `report` | None | No | No |
| `suspect` | `severity` | Yes | No |
| `setter` | `directive`, `execution` | Yes | No |
| `score` | `operator`, `value` | Yes | No |
| `level` | `operator`, `value` | Yes | No |

## `allow`

No configuration. It calls `SetAllow()` on the transaction. The runner stops executing later Actions, Rules, Principles, or Decisions in the current branch.

Place `allow` only where skipping all later checks is intentional.

## `deny`

### Manager Configuration

| Field | Constraint | Default |
| --- | --- | --- |
| `deny_status` | Valid Symfony HTTP status | `403` |
| `deny_content_type` | `json` or `html` | `json` |
| `deny_body` | Required string | None |

Stored JSON:

```json
{
  "status": 403,
  "content_type": "json",
  "body": "{\"message\":\"request denied\"}"
}
```

Defender maps `json` to `application/json` and `html` to `text/html; charset=utf-8`. JSON bodies are validated. Data loaded outside Manager falls back to status `403` and `{"message":"request denied"}` when configuration is missing.

In a request phase, `deny` prevents the backend call and creates a blocking response. In a response phase, it replaces the backend response before it reaches the client.

## `log`

### Manager Configuration

| Field | Constraint | Form Default |
| --- | --- | --- |
| `log_format` | Required string | `[%time%] ...` |
| `log_console` | Required boolean | `true` |
| `log_file` | Required boolean | `true` |

Stored JSON:

```json
{
  "format": "[%time%] %ip% | %method% | %path% | score=%score%",
  "console": true,
  "file": true
}
```

### Record Format

| Tag | Value |
| --- | --- |
| `%pid%` | Defender process ID. |
| `%time%` | `dd/mm/yyyy HH:mm:ss` timestamp. |
| `%referer%` | Request `Referer` header. |
| `%ip%` | Client IP from remote address. |
| `%ips%` | `X-Forwarded-For`, falling back to client IP. |
| `%method%` | HTTP method. |
| `%path%`, `%route%` | Request path. |
| `%score%` | Current violation score. |
| `%protocol%` | Request protocol. |
| `%host%` | Request host. |
| `%url%` | Request URL. |
| `%ua%` | `User-Agent`. |
| `%status%` | Current response status. |
| `%resbody%` | Response body. |
| `%reqheaders%` | Request headers as JSON. |
| `%queryparams%` | Encoded query string. |
| `%body%` | Request body. |
| `%bytesSent%` | Response body length. |
| `%bytesReceived%` | Request body length. |
| `%from%` | Literal `WAF`. |
| `%port%` | Remote-address port. |
| `%reqheader:Name%` | One request header. |
| `%respheader:Name%` | One response header. |
| `%query:Name%` | One query parameter. |
| `%locals:Name%` | Runtime variable created by `setter`. |

Unknown tags remain unchanged. `%%` produces `%`.

### Current Limitation

Runtime prioritizes file output when `file = true`, writing `storage/logs/firewall.log`; without a file path it writes to the console. Manager stores `console`, but the handler does not currently use it independently, so the two switches are not fully independent.

## `request`

### Manager Configuration

| Field | Constraint |
| --- | --- |
| `request_url` | Required string; current validation does not enforce URL syntax. |
| `request_method` | `get`, `post`, `put`, `patch`, `delete`; default `get`. |
| `request_headers` | Optional `{key, value}` list; keys up to 255 characters. |
| `request_body` | Required string, including GET. |

```json
{
  "url": "https://example.com/events",
  "method": "post",
  "headers": [
    { "key": "Content-Type", "value": "application/json" }
  ],
  "body": "{\"event\":\"blocked\"}"
}
```

This sends a side request without changing the main request. Default timeout is 5 seconds.

- For GET, body is parsed as a query string and merged into existing URL query.
- Other methods send the body verbatim.
- Duplicate header keys use the last value.
- The side response is discarded and closed; it does not update the transaction.
- Request creation/send failures are logged only.

## `report`

No Manager configuration. It runs asynchronously and writes a database Report when Defender has a database connection.

The Report contains request/response metadata, headers, bodies, triggering Action, creating Defender, and detailed Rule/Target/Engine/comparator traces. Before writing, it waits up to two minutes by default for the transaction to mark Report data ready.

See [Report](Report.md).

## `suspect`

Required `suspect_severity` accepts:

| Severity | Default Score |
| --- | --- |
| `info` | `1` |
| `notice` | `2` |
| `warning` | `3` |
| `error` | `4` |
| `critical` | `5` |
| `alert` | `6` |
| `emergency` | `7` |

Actual scores come from Defender `PROXY_SEVERITY_*` [environment variables](../Environment-Variables.md#proxy-and-violation-scores). The Action adds that severity value to current score. A missing runtime mapping contributes Go's default `0`.

## `setter`

Setter creates, updates, or removes runtime variables for a later Rule/phase `getter` Target.

### `set` Directive

Required `setter_set` list:

| Field | Constraint |
| --- | --- |
| `key` | Up to 255 characters; form allows letters, numbers, hyphens, underscores. |
| `datatype` | `string` or `number`. |
| `value` | Required string or number at least `1`. |

```json
{
  "directive": "set",
  "execution": [
    { "key": "risk-source", "datatype": "string", "value": "login" },
    { "key": "attempt-count", "datatype": "number", "value": 3 }
  ]
}
```

### `unset` Directive

`setter_unset` lists keys to remove. Stored entries live in `execution`; the handler uses only `key` when directive is `unset`.

Variables exist only in the current HTTP transaction. They are not environment variables and are not persisted.

## `score`

`score_value` is required and at least `1`.

| Operator | Behavior |
| --- | --- |
| `override` | Assign score to `value`. |
| `+` | Add `value`. |
| `-` | Subtract `value`. |
| `*` | Multiply by `value`. |
| `/` | Divide by `value`; division by `0` preserves score. |

Stored JSON uses `operator` and `value`. Subtraction may make score negative; runtime does not clamp it to `0`.

The score after Principles is used by [Decision](Decision.md).

## `level`

`level_value` is numeric and at least `1`; runtime converts the final result to an integer.

| Operator | Behavior |
| --- | --- |
| `override` | Set level to `value`. |
| `increase` | Add `value`. |
| `decrease` | Subtract `value`. |

Results below `1` are clamped to `1`. See [Principle](Principle.md#level-and-execution-order) for runner behavior after increases/decreases.

## Relationship Locking

Action has `is_locked` while attached to a Rule. Detach it from every Rule before editing or deleting it.

## Configuration Checklist

- Place `allow`/`deny` deliberately because they stop later work.
- Ensure `deny` JSON bodies are valid.
- Do not depend on `request` or `report` results in the next Action.
- Match `setter` keys exactly to `getter` Target names.
- Distinguish `suspect` severity increments from direct `score` changes.
- Revalidate Principles after changing Actions or their order.
