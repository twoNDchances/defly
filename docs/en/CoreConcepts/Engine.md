# Engine

An Engine transforms [Target](Target.md) data before [Rule](Rule.md) comparison. It normalizes text, changes datatypes, performs arithmetic, hashes, splits strings, or merges arrays.

```text
Target output -> Engine 1 -> Engine 2 -> ... -> Rule comparator
```

## Configuration Fields

| Field | Required | Meaning |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `input_datatype` | Yes | Accepted datatype: `array`, `number`, or `string`. |
| `type` | Yes | Transformation function; available types depend on input datatype. |
| Parameter field | By type | `position`, `digit`, `hash_method`, or `separator`. |
| `output_datatype` | Yes, read-only | Inferred by Manager and not directly editable. |
| `description` | No | Administration notes. |

Changing `input_datatype` clears selected `type` and `output_datatype`. Selecting a type updates output datatype automatically.

## Datatype Chaining

Every Engine declares `input_datatype` and `output_datatype`. One step's output must match the next step's input.

```text
target string -> trim(string) -> lower(string) -> length(number)
```

This chain is valid; `length` changes `string` to `number`.

```text
target string -> length(number) -> lower(string input)
```

In the second chain, Defender reaches `length`, sees that `lower` requires `string` while the current datatype is `number`, and **stops the entire chain there**. It does not skip an invalid Engine and continue.

The initial datatype comes from Target; when Target uses a [Pattern](Pattern.md), Pattern datatype takes precedence.

## Array Engines

| Type | Parameter | Output | Behavior |
| --- | --- | --- | --- |
| `indexOf` | Required integer `position` | `string` | Gets the item at a position. |
| `merge` | Optional `separator` | `string` | Joins items into one string. |

`position` is zero-based. Negative or out-of-range positions return `nil`; a later string conversion turns this into an empty string.

`merge` converts each item to string first. With no separator, Manager stores `configurations = null` and Defender uses comma `,`.

```text
["admin", "secret"] -> merge("|") -> "admin|secret"
```

## Number Engines

| Type | Parameter | Formula | Output |
| --- | --- | --- | --- |
| `addition` | `digit` | `value + digit` | `number` |
| `subtraction` | `digit` | `value - digit` | `number` |
| `multiplication` | `digit` | `value * digit` | `number` |
| `division` | `digit` | `value / digit` | `number` |
| `powerOf` | `digit` | `value ^ digit` | `number` |
| `remainder` | `digit` | `value mod digit` | `number` |
| `toString` | None | Converts number to string | `string` |

`digit` accepts integers and decimals. Division or remainder by `0` returns `nil`. Arithmetic uses `float64`.

An input that cannot be parsed as a number currently becomes `0`; control Target datatype before arithmetic.

## String Engines

| Type | Parameter | Output | Behavior |
| --- | --- | --- | --- |
| `lower` | None | `string` | Lowercase. |
| `upper` | None | `string` | Uppercase. |
| `capitalize` | None | `string` | Uppercase the first character. |
| `trim` | None | `string` | Remove surrounding whitespace. |
| `trimLeft` | None | `string` | Remove left whitespace. |
| `trimRight` | None | `string` | Remove right whitespace. |
| `removeWhitespace` | None | `string` | Remove all recognized whitespace. |
| `length` | None | `number` | Return string byte count. |
| `hash` | `hash_method` | `string` | Return a lowercase hexadecimal digest. |
| `split` | Optional `separator` | `array` | Split a string into an array. |

`removeWhitespace` splits on whitespace and rejoins without a separator. `length` uses Go byte length, so Unicode strings may be longer than their visible character count.

`split` defaults to comma `,`. If the separator does not occur, output is a one-item array containing the full string.

## Hash Methods

`hash` supports:

- `md5`
- `sha1`
- `sha224`
- `sha256`
- `sha512`

Missing or unknown runtime methods fall back to `sha256`. MD5 and SHA-1 are suitable only for compatible normalization/comparison, not password storage or new cryptographic uses.

## Stored `configurations`

Manager converts form fields to JSON:

```json
{ "position": 0 }
```

```json
{ "digit": 10.5 }
```

```json
{ "hash_method": "sha256" }
```

```json
{ "separator": "|" }
```

Parameterless Engines, and `merge`/`split` without a separator, store `configurations` as `null`.

## Attachment Order on Target

Engines attach through a pivot with `order`. The same set in another order can produce different values and datatypes.

```text
" A,B " -> trim -> split(",") = ["A", "B"]
" A,B " -> split(",") -> ... = [" A", "B "]
```

[Principle validation](Principle.md#validation) checks the input/output datatype chain. Revalidate after changing Engine order.

## Relationship Locking

Engine has `is_locked`. Manager locks it while attached to any Target. Detach it from every Target before editing or deleting it.

## Configuration Checklist

- Select a type valid for the input datatype.
- Ensure output matches the next Engine and final comparator.
- Use zero-based `position` for `indexOf`.
- Avoid `division` and `remainder` with `digit = 0`.
- Set a separator when comma is unsuitable.
- Revalidate Principles after changing an Engine or chain order.
