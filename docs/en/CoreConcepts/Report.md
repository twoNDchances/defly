# Report

A Report is a firewall-event snapshot created by the [`report` Action](Action.md#report). It stores request, response, and triggering Rule traces to explain why traffic was flagged.

Report is runtime data and cannot be created or updated manually through policy/UI. Authorized users may view, mark reviewed, or delete it.

## Stored Fields

| Field | Type | Meaning |
| --- | --- | --- |
| `metas` | JSON object | IP, URL, HTTP status, method, protocol. |
| `request_headers` | JSON array | Request `{key, value}` headers. |
| `request_body` | JSON object | Parsed/classified request body. |
| `response_headers` | JSON array | Response `{key, value}` headers. |
| `response_body` | JSON object | Parsed/classified response body. |
| `rule_details` | JSON object | Target output, Engine trace, comparator, and matched values. |
| `triggered_by` | Nullable UUID | Creating `report` [Action](Action.md). |
| `created_by` | Nullable UUID | Creating [Defender](Defender.md). |
| `is_reviewed` | Boolean | Whether an operator reviewed it. |

Report has UUID and timestamps. Unlike most Manager models, `created_by` points to Defender rather than User.

## Metadata

Runtime stores:

```json
{
  "ip": "203.0.113.10",
  "url": "https://example.com/login?next=/admin",
  "status": 403,
  "method": "POST",
  "protocol": "HTTP/1.1"
}
```

URL prefers the transaction's complete URL. Otherwise Defender composes scheme, host, and raw URL; scheme falls back to `http` and empty path to `/`.

## HTTP Headers

Each header key becomes one item; multiple values join with `; `.

```json
[
  { "key": "Content-Type", "value": "application/json" },
  { "key": "X-Forwarded-For", "value": "203.0.113.10; 10.0.0.5" }
]
```

Runtime map order is not stable.

## Request Body

| Content | Report Structure |
| --- | --- |
| JSON object | Stored directly. |
| JSON array/scalar | `{ "body": <decoded-value> }`. |
| URL-encoded form | Field object; multi-values become arrays. |
| Multipart | `{ "fields": {...}, "files": {...} }`. |
| Other | `{ "body": "<raw text>" }`. |

Multipart files store `filename`, `size`, and string `content`. Reports can contain sensitive or large files, so control retention and viewing permissions.

## Response Body

JSON follows request handling. Non-JSON uses a key based on content type:

| Content Type | Key |
| --- | --- |
| HTML/XHTML | `html` |
| Plain text | `text` |
| XML | `xml` |
| Image/audio/video/PDF/zip/octet-stream | `file` |
| Other | `body` |

An empty response body stores an empty object.

## `rule_details`

The trace contains:

### Rule

```json
{
  "id": "<rule-uuid>",
  "name": "detect-login-attack",
  "phase": 3,
  "is_inversed": false
}
```

### Target

ID, name, phase, type, and datatype; when [Pattern](Pattern.md) or [Wordlist](Wordlist.md) exists, its metadata is included.

### Values and Engine Chain

- `target_output`: extractor result before Engines.
- `engine_chain`: each Engine ID/name/type, input/output datatype, input, output.
- `final_output`: value after final Engine.
- `datatype`: final datatype.

### Comparator and Matches

- `comparator`: Rule comparator.
- `expected_values`: configured or Wordlist values.
- `matched_values.target`: matched Target values.
- `matched_values.expected`: matched expected values.
- `matched_context`: shortened context using `...` around matches.

When expected values exceed 10 items, Report does not copy all of them; it retains ellipses and matched items to limit display size.

## Creation Timing

The `report` Action runs asynchronously. It waits up to two minutes by default for Report data readiness, then opens a database connection with roughly a three-second write timeout.

With an empty connection string, no Report is created. Connection/write failures are logged and do not block the main request.

## Review Reports

Manager's `review` action sets `is_reviewed = true` and creates a [Timeline](Timeline.md) action `review`. Review is one-way in the current UI; the button disappears after review.

Related permissions:

- `viewAny`, `view`: list/view Reports.
- `deleteAny`, `delete`: delete Reports.
- `review`, `reviewAny`: mark reviewed.

Report does not support `create` or `update`.

## API Under Defender

Report API is nested under Defender. View/delete verifies `report.created_by` equals the Defender in the URL; a Report from another Defender returns `404`/is inaccessible through that path.

## Sensitive Data

Reports may contain authentication headers, cookies, passwords, tokens, personal data, and uploads. Runtime currently records comprehensive data without automatic redaction.

Recommendations:

- Grant Report access only to required investigators.
- Define deletion/retention policy.
- Use `report` only on Rules requiring evidence.
- Do not send Reports directly to unprotected logs or notifications.

## Investigation Checklist

- Confirm `triggered_by` and source Defender.
- Read `target_output`, then each Engine step and `final_output`.
- Compare comparator with `expected_values` and `matched_values`.
- Check `is_inversed` before concluding Rule logic.
- Mark reviewed after handling to separate new and old queues.
