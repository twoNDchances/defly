# Target

A Target defines **which data Defender extracts** from the HTTP lifecycle for a [Rule](Rule.md) to compare. It does not decide whether a request is valid; it extracts a value and sends it through an [Engine](Engine.md) chain.

```text
HTTP transaction -> Target -> Engine chain -> Rule comparator
```

A Target can read request data, response data, or a runtime variable created earlier by the [`setter` Action](Action.md#setter).

## Configuration Fields

| Field | Required | Meaning |
| --- | --- | --- |
| `phase` | Yes | HTTP lifecycle phase where data exists, `1` through `6`. |
| `type` | Yes | Data source: `getter`, `full`, `header`, `meta`, `query`, `body`, or `file`. |
| `pattern_id` | By type | Built-in [Pattern](Pattern.md) for aggregate data; required for `full` and `meta`. |
| `name` | Yes | Unique Target name and the key to read when no Pattern is used. |
| `datatype` | Yes | Declared output datatype: `array`, `number`, or `string`. |
| `wordlist_id` | Conditional | Required for an `array` Target without a Pattern. |
| `description` | No | Administration notes. |

`name` is unique lowercase kebab-case up to 255 characters, for example `authorization-header` or `request-credentials`.

Selecting a Pattern makes Manager set and lock `datatype` to the Pattern datatype. At runtime, Defender also prefers Pattern datatype over the value stored directly on Target.

## Six HTTP Phases

| Phase | Name | Available Data |
| --- | --- | --- |
| `1` | Full request | Complete raw request. |
| `2` | Request headers | Headers, query string, and request metadata. |
| `3` | Request body | Request body and uploaded files. |
| `4` | Response headers | Headers and response metadata. |
| `5` | Response body | Response body. |
| `6` | Full response | Complete raw response. |

Target phase must match the [Rule](Rule.md) and [Principle](Principle.md) that use it. Reading a Target in another phase returns `nil`, so its Rule normally does not match.

## Valid Types by Phase

| Phase | Valid Types |
| --- | --- |
| `1` | `getter`, `full` |
| `2` | `getter`, `full`, `header`, `meta`, `query` |
| `3` | `getter`, `full`, `body`, `file` |
| `4` | `getter`, `full`, `header`, `meta` |
| `5` | `getter`, `full`, `body` |
| `6` | `getter`, `full` |

Manager displays only types valid for the phase. Changing phase resets type to `getter` and clears Pattern; changing type also clears Pattern to prevent an invalid combination.

## Type Semantics

### `getter`

Reads a runtime variable whose key matches Target `name`. A `setter` Action must create it earlier in the **same HTTP transaction**.

For example, an Action in phase `2` sets `authenticated-user = admin`; a phase `3` `getter` Target named `authenticated-user` reads `admin`.

A missing variable returns `nil`. Runtime variables do not persist to the next request.

### `full`

Reads the complete representation of a phase. A Pattern is required to distinguish raw request, raw response, all headers, or full body.

Legacy/external data containing a `full` Target without a Pattern returns `nil` instead of being guessed.

### `header`

Reads an HTTP header by `name`, case-insensitively through header-name normalization.

- One value returns a string.
- Multiple values for the same header return an array.
- A missing header returns `nil`.
- A Pattern may return all header keys, values, or their count.

### `meta`

Reads HTTP metadata outside headers and bodies. Manager requires a Pattern.

Available Patterns include method, protocol, IP, path, URL scheme, host, port, and response status. See the [Pattern catalog](Pattern.md#built-in-pattern-catalog).

The runtime extractor also understands direct keys such as `method`, `protocol`, `path`, `url`, `host`, `scheme`, `port`, `ip`, `remote_addr`, `content_length`, `status`, and `status_code`; the standard form still requires a Pattern for `meta`.

### `query`

Reads parameters after `?` in the request URL. Without a Pattern, `name` selects one parameter.

For `/search?q=defly&page=2`, a Target named `q` returns `defly`. A missing key returns an empty string following `url.Values.Get` behavior.

### `body`

Reads a request body field in phase `3` or a response body field in phase `5`.

Defender parses:

| Content Type | Behavior |
| --- | --- |
| `application/json` | Reads a JSON object; non-object JSON is stored under `body`. |
| `application/x-www-form-urlencoded` | Reads form fields; multi-valued fields become arrays. |
| `multipart/form-data` | Reads non-file form parts only. |
| Other | Stores the entire body under `body`. |

Body Targets support dot-separated paths. `profile.email` reads `email` from `profile`; `items.0.name` reads `name` from the first `items` element.

### `file`

Valid only for phase `3` request bodies and only for named file parts in `multipart/form-data`.

One file field returns its content as a string; multiple files under the same field return an array. Filename, extensions, detected extensions, count, and total length are available through [Patterns](Pattern.md).

## Datatypes

| Datatype | Meaning | Final Comparator |
| --- | --- | --- |
| `array` | List of values. | Array comparators in [Rule](Rule.md#array-comparators). |
| `number` | Number represented as `float64` in Defender. | Number comparators. |
| `string` | One string. | String comparators. |

Datatype is the contract between Target and the first Engine. Defender converts extraction output to this datatype before running Engines. A wrong declaration can create unexpected values; for example, a nonnumeric string becomes `0` in a numeric Engine.

## Pattern and Wordlist

A Target has three extraction modes:

1. **With Pattern:** Pattern controls extractor and datatype; no Wordlist is needed.
2. **No Pattern, `array`:** a [Wordlist](Wordlist.md) is required, and each line is a key to read.
3. **No Pattern, `string` or `number`:** Target `name` is the single key to read.

For a Wordlist-backed array, Defender preserves Wordlist order. Every key creates an output position; a missing key becomes `""`.

Wordlist:

```text
username
password
otp
```

Request body:

```json
{
  "username": "admin",
  "password": "secret"
}
```

Target output:

```json
["admin", "secret", ""]
```

By contrast, Pattern `request-body-keys` returns actual keys rather than user-listed keys:

```json
["username", "password"]
```

## Attach Engines

A Target attaches multiple Engines through `targets_engines`, whose `order` field controls execution.

Example:

```text
request header User-Agent
  -> trim
  -> lower
  -> length
  -> number
```

The chain's final output datatype determines allowed Rule comparators. See [Engine](Engine.md#datatype-chaining).

## Relationship Locking

Target has `is_locked`. Manager locks it while referenced by a Rule. Protected workflows cannot update/delete a locked Target; detaching all references synchronizes the lock state.

Wordlists and Engines attached to a referenced Target are also locked. This prevents silent changes to an assembled policy.

## Configuration Checklist

- Select the phase in which data exists.
- Select only a type valid for that phase.
- Use a required Pattern for `full` and `meta`.
- Use a Wordlist for an `array` Target without a Pattern.
- Check Pattern datatype or final Engine output before creating a Rule.
- Revalidate Principles after changing Target or Engine order.
