# Wordlist

A Wordlist is an ordered list of strings used in two places:

- An `array` [Target](Target.md) without a Pattern: each item names a field to extract.
- A [Rule](Rule.md): each item is an expected value or regular expression.

## Configuration Fields

| Field | Required | Meaning |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `type` | Yes | `file` or `json`; the form defaults to `file`. |
| `word_file` | For `file` | Text file uploaded to the `wordlists` directory. |
| `word_json` | For `json` | At least one `{ "word": "..." }` object. |
| `word_count` | System | Item count calculated by Manager on save. |
| `description` | No | Administration notes. |

Each `word_json.*.word` is a required string up to 255 characters. File uploads accept MIME type `text/plain`.

## JSON Wordlist

Stored example:

```json
[
  { "word": "username" },
  { "word": "password" },
  { "word": "otp" }
]
```

Defender preserves array order and reads each object's `word` field.

## File Wordlist

Each line is one item. Defender reads lines in order, removes line endings, and preserves the rest.

```text
username
password
otp
```

Defender searches for the file in this order:

1. Absolute path.
2. Root configured by `WORDLIST_ROOT`.
3. Current relative path.
4. `storage/app/public/<path>`.
5. `../manager/storage/app/public/<path>`.
6. `../manager/storage/app/private/<path>`.

If the file is missing or unreadable, the loader logs the error and returns an empty list.

## `word_count`

For files, Manager trims lines and counts non-empty entries. For JSON, it counts array items. Changing type clears the other representation; replacing a file deletes the old file, and deleting a Wordlist deletes its associated file.

Runtime differs slightly: Defender currently includes empty file lines while Manager excludes them from `word_count`. Remove blank lines so count and behavior remain consistent.

## Use with Target

For an `array` Target without a Pattern, the Wordlist contains **keys to read**, not expected values.

```text
username
password
```

Given request body `{"username":"admin","password":"secret"}`, the Target returns:

```json
["admin", "secret"]
```

A missing key still produces an empty string to preserve position. See [Target](Target.md#pattern-and-wordlist).

## Use with Rule

| Comparator | Meaning of Each Line |
| --- | --- |
| `@similar` | String compared for equality with a Target array item. |
| `@search` | Regular expression applied to each Target array item. |
| `@check` | String compared for equality with a string Target. |
| `@checkRegExp` | Regular expression applied to a string Target. |

Regular-expression comparators use Go RE2 syntax. An invalid expression line is treated as non-matching and does not stop Defender.

## Validation and Locking

[Principle validation](Principle.md#validation) checks:

- The Wordlist exists and has the correct type.
- A file has a path, exists, is readable, and has suitable content.
- JSON is a list whose items contain valid `word` fields.
- Data count matches required metadata.

Wordlist has `is_locked` and locks while referenced by a Target or Rule. Detach every reference before editing or deleting it.

## Checklist

- Use clean files without unintended blank lines.
- Do not add commas or quotes unless they are part of a word.
- Distinguish Target key Wordlists from Rule expected-value Wordlists.
- Test every regular-expression line against RE2.
- Revalidate Principles after changing Wordlist content.
