# Rule

A Rule is a firewall comparison. It takes [Target](Target.md) output after its [Engine](Engine.md) chain, compares it with a configured value or [Wordlist](Wordlist.md), and returns `true` or `false` to a [Principle](Principle.md).

```text
Target -> Engine chain -> Comparator -> true/false
```

## Configuration Fields

| Field | Required | Meaning |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `phase` | Yes | Phase `1` through `6` where the Rule evaluates. |
| `target_id` | Yes | Same-phase Target supplying the value. |
| `comparator` | Yes | Comparator valid for the Target's final datatype. |
| `is_inversed` | Yes | Inverts the Rule result; default `false`. |
| Expected-value field | By comparator | String, number, range, or Wordlist. |
| `description` | No | Administration notes. |

Manager shows initial and final Target datatypes. Comparator options are filtered by final datatype. Changing Target clears the old comparator.

Target must exist in the same phase as Rule. A Principle using the Rule must also share that phase.

## Stored Expected Values

Manager maps form fields into `configurations`:

| Comparator | Form Field | Stored JSON |
| --- | --- | --- |
| `@equal`, `@greaterThan`, `@lessThan`, `@greaterThanOrEqual`, `@lessThanOrEqual` | `number_value` | `{ "number": ... }` |
| `@inRange` | `number_from_value`, `number_to_value` | `{ "number_from": ..., "number_to": ... }` |
| `@contains`, `@match`, `@mirror`, `@startsWith`, `@endsWith`, `@regExp` | `string_value` | `{ "string": "..." }` |
| `@similar`, `@search`, `@check`, `@checkRegExp` | `wordlist_id` | `configurations = null`; expected data comes from Wordlist |

For `@inRange`, `from` must be less than `to`; both endpoints are included.

## Array Comparators

| Comparator | Expected Data | True When |
| --- | --- | --- |
| `@similar` | Wordlist | Any Target item equals any Wordlist word. |
| `@contains` | String | Any Target item exactly equals the configured string. |
| `@match` | Regular expression | Any Target item matches the expression. |
| `@search` | Regex Wordlist | Any Target item matches any Wordlist expression. |

`@contains` can be misleading: current source checks **array-item equality**, not substring containment.

Given:

```json
["username", "password"]
```

- `@contains` with `password` is `true`.
- `@contains` with `pass` is `false`.
- `@match` with `^pass` is `true`.

## Number Comparators

| Comparator | True When |
| --- | --- |
| `@equal` | Target equals the configured number. |
| `@greaterThan` | Target is greater. |
| `@lessThan` | Target is less. |
| `@greaterThanOrEqual` | Target is greater than or equal. |
| `@lessThanOrEqual` | Target is less than or equal. |
| `@inRange` | `from <= target <= to`. |

Defender parses `float64` and permits surrounding whitespace in numeric strings. Unparseable Target or expected values return `false`.

## String Comparators

| Comparator | Expected Data | True When |
| --- | --- | --- |
| `@mirror` | String | Target exactly equals the string. |
| `@startsWith` | String | Target begins with the string. |
| `@endsWith` | String | Target ends with the string. |
| `@check` | Wordlist | Target equals one Wordlist word. |
| `@regExp` | Regular expression | Target matches the expression. |
| `@checkRegExp` | Regex Wordlist | Target matches one Wordlist expression. |

String comparisons are case-sensitive. Attach `lower` or `upper` to Target and normalize expected values for case-insensitive behavior.

## Regular Expressions

Defender compiles expressions with Go regexp, which uses RE2 syntax. An invalid expression does not escape as an error; the comparator returns `false`.

For regex Wordlists, every line is independent. One valid matching expression makes the Rule true; invalid lines count as non-matches.

## Inversion with `is_inversed`

Inversion occurs in Principle after comparison:

```text
matched = comparator(target, expected)
if is_inversed:
    matched = !matched
```

If `@mirror = admin` returns `true`, `is_inversed = true` makes the Rule result `false` inside Principle.

## Attach Actions

A Rule attaches multiple [Actions](Action.md) through `rules_actions`, whose `order` field controls execution. Actions do not run immediately when that individual Rule matches.

Defender:

1. Evaluates every Principle Rule with AND.
2. Only if all match, runs each Rule's Actions in Rule order and Action order.

This prevents early side effects before a later Rule fails.

## Relationship Locking

Rule has `is_locked` while attached to a Principle. Manager also synchronizes locking for related Target, Wordlist, and Actions.

## Configuration Checklist

- Rule, Target, and Principle phases must match.
- Comparator must match the final Engine datatype.
- Select a Wordlist for the four list comparators.
- Ensure `from < to` for `@inRange`.
- Validate expressions against RE2 syntax.
- Enable `is_inversed` only to negate the entire comparison.
- Revalidate Principles after changing Target, comparator, Wordlist, or Action.
