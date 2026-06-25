# Defender Guide

Defender is a Go program running a control API, reverse proxy, and runtime firewall. The deployment record is documented in [Defender](CoreConcepts/Defender.md).

## Runtime Components

- Control server: internal API on port `9947` by default.
- Proxy: application traffic port, default `9948` when run manually.
- Firewall core: captures HTTP transactions and runs phases, Principles, Actions, and Decisions.
- Doctor: monitors health and detects abnormal status.
- Log and error writers: persist operational data.

Do not expose the control API publicly. Manager is its primary caller and can verify TLS as described in [Configuration](Configuration.md#manager-and-defender).

## HTTP Transaction Initialization

When the proxy receives a request, Defender captures the raw request, body, URL, port, and metadata. It reads and restores the body so the backend still receives the data.

The HTTP transaction stores:

- Current request and response.
- Raw request and response.
- Score and level.
- Runtime variables for `setter`/`getter`.
- Allow, deny, cancel, rewrite, and response flags.

## Request Phases

1. Phase `1`: full request.
2. Phase `2`: request headers, query parameters, and metadata.
3. Phase `3`: request body and files.

In each phase, Defender runs [Principles](CoreConcepts/Principle.md) for the current phase and level. After phase `3`, it evaluates request-direction [Decisions](CoreConcepts/Decision.md).

On `deny`, Defender returns a blocking response. On `cancel`, it closes the connection. If allowed, the request may be rewritten before being forwarded to the backend.

## Response Phases

Defender captures the backend response, decompresses the body when necessary for inspection, and runs:

4. Phase `4`: response headers and metadata.
5. Phase `5`: response body.
6. Phase `6`: full response.

After response-direction Decisions, Defender applies header/body rewrites, blocking, no-cache behavior, or cookie expiration, then restores body encoding before returning data to the client.

## Target and Engine

A [Target](CoreConcepts/Target.md) reads data only in its matching phase. With a [Pattern](CoreConcepts/Pattern.md), Defender invokes the named extractor. For an array Target using a [Wordlist](CoreConcepts/Wordlist.md), each line is a key to read.

The value then passes through the [Engine](CoreConcepts/Engine.md) chain. The chain stops on a datatype mismatch.

## Rule and Principle

A [Rule](CoreConcepts/Rule.md) compares the final value with an expected value. Rules inside a Principle combine with AND. Defender runs Actions only after every Rule matches.

Principles run in ascending level order. The `level` Action can expand or restrict the remaining Principles in the phase without rerunning completed work.

## Action and Decision

An [Action](CoreConcepts/Action.md) runs inside a Principle and can log/report, set variables, add scores, or block immediately. A [Decision](CoreConcepts/Decision.md) runs after Principles and uses the total score.

This distinction separates detection from verdicts: multiple Rules may contribute scores before a Decision allows, blocks, or rewrites data.

## Reports and Investigation Files

The `report` Action stores a [Report](CoreConcepts/Report.md) in the database. The `save` Decision writes raw requests under `storage/requests`. Firewall logs may use `storage/logs/firewall.log` by default.

These files may contain secrets and user content; see [Security](Security.md#waf-data-and-privacy).

## Run Manually

See [manual Defender installation](Installation.md#4-defender). Manager must migrate and seed the database first.

## Test Changes

From `defender`:

```powershell
go test ./...
```

For firewall changes, run at least:

```powershell
go test ./internal/firewall/...
```

Changes to Patterns, comparators, Actions, or Decisions require tests for input data, HTTP transaction results, and report data.
