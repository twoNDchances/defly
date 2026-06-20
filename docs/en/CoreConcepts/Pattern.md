# Pattern

A Pattern is a built-in extractor with a fixed name. It represents aggregate values that cannot be described only through Target `phase`, `type`, `datatype`, and a field name, such as a raw request, body keys, HTTP method, or total uploaded-file size.

A Pattern is not a regular expression. Regular expressions belong to [Rule](Rule.md) comparators.

## Attributes

| Field | Meaning |
| --- | --- |
| `name` | Name Defender uses to select an extractor; unique, up to 255 characters. |
| `phase` | Phase in which the data is available. |
| `type` | Source type; cannot be `getter`. |
| `datatype` | Returned datatype: `array`, `number`, or `string`. |
| `description` | Bilingual description supplied by seed data. |

Patterns form a system catalog created by `PatternSeeder`. They have no `create`, `update`, or `delete` Permission; administrators select existing Patterns instead of creating extractors in the UI.

## How Target Uses a Pattern

Manager lists only Patterns matching the Target phase and type. On selection:

- Manager sets Target datatype from Pattern datatype.
- Target does not need a Wordlist.
- Defender invokes the extractor by **Pattern name**.
- Pattern datatype becomes the Engine chain input datatype.

Targets of type `full` and `meta` require a Pattern. Other types may use an aggregate Pattern or read a user-named key without one.

## Built-in Pattern Catalog

### Phase 1: Full Request

| Pattern | Type | Datatype | Result |
| --- | --- | --- | --- |
| `request-full` | `full` | `string` | Raw request line, HTTP headers, and body stored by the transaction. |

### Phase 2: Request Headers, Query, and Metadata

| Pattern | Type | Datatype | Result |
| --- | --- | --- | --- |
| `request-header-keys` | `header` | `array` | Request header names. |
| `request-header-values` | `header` | `array` | All request header values. |
| `request-header-size` | `header` | `number` | Header-key count, not total value count. |
| `request-query-keys` | `query` | `array` | Query parameter names. |
| `request-query-values` | `query` | `array` | All query parameter values. |
| `request-query-size` | `query` | `number` | Query-key count. |
| `request-meta-url-port` | `meta` | `number` | Request URL port. |
| `request-meta-protocol` | `meta` | `string` | Protocol such as `HTTP/1.1`. |
| `request-meta-ip` | `meta` | `string` | Remote address; IPv6 loopback `::1` normalizes to `127.0.0.1`. |
| `request-meta-method` | `meta` | `string` | HTTP method. |
| `request-meta-url-path` | `meta` | `string` | URL path. |
| `request-meta-url-scheme` | `meta` | `string` | URL scheme such as `http` or `https`. |
| `request-meta-url-host` | `meta` | `string` | Request host. |
| `request-full-headers` | `full` | `string` | All request headers as `Key: Value\r\n`. |

Go map key/value order is not stable. Do not make policy depend on Pattern array positions when extracting header/query keys or values.

### Phase 3: Request Body and Files

| Pattern | Type | Datatype | Result |
| --- | --- | --- | --- |
| `request-body-keys` | `body` | `array` | Keys of non-file body fields. |
| `request-body-values` | `body` | `array` | Body field values converted to strings. |
| `request-body-size` | `body` | `number` | Number of body fields. |
| `request-body-length` | `body` | `number` | Raw request body byte count. |
| `request-full-body` | `full` | `string` | Raw request body. |
| `request-file-keys` | `file` | `array` | Multipart field names containing files. |
| `request-file-values` | `file` | `array` | Each file's content as a string. |
| `request-file-names` | `file` | `array` | Client-supplied filenames. |
| `request-file-extensions` | `file` | `array` | Lowercase filename extensions without dots. |
| `request-file-detected-extensions` | `file` | `array` | Extensions detected from MIME content. |
| `request-file-size` | `file` | `number` | Total file parts, not field count. |
| `request-file-name-size` | `file` | `number` | Filename count, currently equal to file-part count. |
| `request-file-length` | `file` | `number` | Total bytes across all file contents. |

File Patterns require valid `multipart/form-data` with a correct boundary. `request-file-detected-extensions` omits items whose extension cannot be detected.

### Phase 4: Response Headers and Metadata

| Pattern | Type | Datatype | Result |
| --- | --- | --- | --- |
| `response-header-keys` | `header` | `array` | Response header names. |
| `response-header-values` | `header` | `array` | All response header values. |
| `response-header-size` | `header` | `number` | Response header-key count. |
| `response-meta-status` | `meta` | `number` | HTTP status code. |
| `response-meta-protocol` | `meta` | `string` | Response protocol. |
| `response-full-headers` | `full` | `string` | All response headers as `Key: Value\r\n`. |

### Phase 5: Response Body

| Pattern | Type | Datatype | Result |
| --- | --- | --- | --- |
| `response-body-keys` | `body` | `array` | Parsed response-body keys. |
| `response-body-values` | `body` | `array` | Parsed response values converted to strings. |
| `response-body-size` | `body` | `number` | Number of response-body fields. |
| `response-body-length` | `body` | `number` | Raw response-body byte count. |
| `response-full-body` | `full` | `string` | Raw response body. |

Response bodies follow the JSON, URL-encoded form, and fallback `body` parsing rules in [Target](Target.md#body).

### Phase 6: Full Response

| Pattern | Type | Datatype | Result |
| --- | --- | --- | --- |
| `response-full` | `full` | `string` | Raw response status line, headers, and body stored by the transaction. |

## Unknown Pattern

Defender selects extractors with a `switch` on Pattern name. A database Pattern without matching Defender implementation returns `nil`. Adding a Pattern therefore requires Manager seed/migration data and a corresponding Defender extractor.

## Validation

[Principle validation](Principle.md#validation) checks:

- Pattern exists.
- Pattern is not attached to a `getter` Target.
- Pattern phase and type match Target.
- Target datatype matches Pattern datatype.
- `full` and `meta` Targets have a Pattern.

## Choose Pattern or Wordlist

Use Pattern for **one built-in aggregate extraction**, such as all body keys. Use [Wordlist](Wordlist.md) to **list specific keys**, such as only `username`, `password`, and `otp`.

```text
Pattern request-body-keys -> ["username", "password", "remember"]
Wordlist username/password -> ["admin", "secret"]
```

## Checklist

- Match Pattern phase and type.
- Check Pattern datatype before attaching Engines.
- Do not confuse `size` with byte length; the catalog distinguishes `size` and `length`.
- Do not depend on Go map key/value order.
- Implement a corresponding Defender extractor for every new Pattern.
