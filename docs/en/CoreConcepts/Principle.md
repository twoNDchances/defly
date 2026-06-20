# Principle

A Principle combines multiple same-phase [Rules](Rule.md) into one AND condition. It is the policy unit attached to [Defender](Defender.md), validated, and applied/revoked independently.

```text
Principle = Rule 1 AND Rule 2 AND ... AND Rule n
```

## Configuration Fields

| Field | Required | Meaning |
| --- | --- | --- |
| `name` | Yes | Unique lowercase kebab-case, up to 255 characters. |
| `level` | Yes | Execution level, integer at least `1`; default `1`. |
| `phase` | Yes | One of six HTTP phases; default `1`. |
| `validation_status` | System | `pending`, `validating`, `failed`, or `passed`; read-only. |
| `validation_details` | System | Validation result JSON; read-only. |
| `description` | No | Administration notes. |

Rules attach through ordered `principles_rules`. Principles attach to Defender through `defenders_principles` with `order` and `is_applied`.

## AND Operator

Defender evaluates attached Rules in order:

1. Skip `nil` Rules or Rules from another phase.
2. Extract Target, run Engines, and compare.
3. Invert when `is_inversed = true`.
4. On one false Rule, stop the Principle and run none of its Actions.
5. If every evaluated Rule is true, run each Rule's Actions in order.

A Principle without Rules is skipped. Deferring all Actions until the end prevents early side effects before a later Rule fails.

## Phase

A Principle runs only in its declared phase. Rules and Targets inside must share that phase; [validation](#validation) detects mismatches.

The six phases are documented in [Target](Target.md#six-http-phases). A `setter` Action and `getter` Target can pass data from an earlier phase to a later phase in the same HTTP transaction.

## Level and Execution Order

Levels tier policy by strictness. A transaction starts at `PROXY_VIOLATION_LEVEL`, default `1`. Within each phase, Defender runs levels from `1` through the current level.

Level does not mean starting at the current level and skipping lower levels. At level `3`, Defender evaluates levels `1`, `2`, then `3`.

### When Level Increases

After completing one level, the runner reads the current level again. If `level` increases from `1` to `3`, levels `2` and `3` join the current phase's execution scope.

```text
level 1 -> action raises to 3 -> level 2 -> level 3
```

### When Level Decreases

Before each Principle and after each level, the runner checks current level. If it falls below the level being evaluated, the phase stops; completed Principles do not run again.

```text
level 1 -> level 2 -> action lowers to 1 -> stop
```

Within one level, Principles retain Defender relationship order. Level selects which Principles run; score is the number used by [Decision](Decision.md).

## Validation

A Principle has four states:

| Status | Meaning |
| --- | --- |
| `pending` | Queued or waiting to start validation. |
| `validating` | A job is checking policy structure. |
| `failed` | At least one error or validation exception occurred. |
| `passed` | No structural error was found. |

Validation runs as a queue job. It sets `validating` at start, then `passed` or `failed` with `validation_details`. Exceptions produce `failed` and include the exception class/message.

Validation cannot be requested while status is `pending` or `validating`; update/delete are also blocked in those states.

### What Validation Checks

At minimum:

- Principle phase is valid.
- Rules exist, share the phase, and use a comparator valid for the final datatype.
- Targets exist, share the phase, and use a valid phase/type combination.
- `full`/`meta` Targets have required Patterns.
- Pattern phase, type, and datatype match Target; `getter` does not use Pattern.
- Array Targets without Pattern have valid Wordlists.
- Engine input/output datatypes form a valid chain.
- Wordlist comparators have a Wordlist.
- Wordlist files exist/read correctly and JSON has valid structure/count.
- Action types belong to the supported catalog.

`validation_details` contains status, time, errors with `code`, `message`, `context`, and totals for checked Rules, Targets, Engines, Actions, and Wordlists.

`passed` confirms structural consistency at validation time; it does not prove correct detection for all real traffic.

## Attach and Apply to Defender

The lifecycle has two distinct steps:

1. **Attach:** create the Defender-Principle relationship and set `order`.
2. **Apply:** Manager sends the Principle ID to Defender and sets `is_applied = true` on success.

Only `passed` Principles are listed/applied through the standard flow. Defender must also have `deployment_status = successful`.

`revoke` removes the Principle from runtime and is valid only when already applied; success sets `is_applied = false`. Attached does not mean running, and detaching should not replace revocation while policy remains active.

See [Defender](Defender.md#policy-management) for communication details.

## Relationship Locking

Principle has `is_locked` while attached to any Defender. Attached Rules are also locked. Detach through the correct lifecycle before editing structure, then revalidate.

## Operations Checklist

- Every Rule and Target must match Principle phase.
- Order Rules for understandable diagnostics even though logic is AND.
- Reach `passed` before attach/apply.
- Revalidate after changing related Target, Engine, Rule, Action, or Wordlist.
- Distinguish attach from apply and revoke from detach.
- Review `level` Actions to avoid unintentionally expanding or stopping the current phase.
