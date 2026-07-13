# Manager Guide

Manager is the Laravel/Filament interface for access administration, policy construction, [Defender](CoreConcepts/Defender.md) deployment, and [Report](CoreConcepts/Report.md) investigation.

Default Compose address:

```text
https://localhost/defly-manager
```

## Start with the Dashboard

Use the dashboard to review the system before changing configuration: Defender health, deployment status, recent reports, and trends. Charts expose time or scope filters when their data supports filtering.

The dashboard does not replace logs. When a status is abnormal, open the Defender, deployment details, or related report.

## Access Administration

Read these concepts in order:

1. [User](CoreConcepts/User.md)
2. [Group](CoreConcepts/Group.md)
3. [Permission](CoreConcepts/Permission.md)
4. [Guard](CoreConcepts/Guard.md)
5. [Key](CoreConcepts/Key.md)

Prefer permissions through Groups. Assign them directly to a User or Key only for a clear exception. Every API Key should have an expiry, purpose, and explicit owner.

Use Guards when a Defender needs a smaller operator set than the general Defender
Permission holders.

## Resource Classification

[Labels](CoreConcepts/Label.md) group configuration by application, environment, or responsible team. Labels do not affect WAF execution and do not replace permissions.

## Build a Policy

Do not start from Principle. Build from data toward the final verdict.

### 1. Reusable Data

- [Wordlist](CoreConcepts/Wordlist.md) for lists of keys, values, or regular expressions.
- [Pattern](CoreConcepts/Pattern.md) for built-in extractors already supported by Defender.

System Patterns are commonly locked because they must remain synchronized with Defender source code.

### 2. Target

A [Target](CoreConcepts/Target.md) selects phase, type, and datatype. A Pattern determines the datatype when present. An array Target without a Pattern requires a Wordlist containing field names.

After selecting a Target, attach ordered [Engines](CoreConcepts/Engine.md) to normalize the data. Check each step's input and output datatypes.

### 3. Rule

A [Rule](CoreConcepts/Rule.md) combines a Target, comparator, expected value, or Wordlist. The comparator must match the datatype after the Engine chain.

Name a Rule after its condition rather than its action. For example, `request-body-has-password-field` is clearer than `deny-bad-request`.

### 4. Action

An [Action](CoreConcepts/Action.md) describes what happens when a Rule matches. During testing, prefer `log`, `report`, or `suspect` before `deny`.

`allow` and `deny` stop later Actions, so attachment order matters.

### 5. Principle

A [Principle](CoreConcepts/Principle.md) combines Rules in the same phase with AND. Choose a level for strictness, order the Rules, and validate the Principle.

Do not apply a Principle in `pending`, `validating`, or `failed` state. Validate it again after changing a dependency.

### 6. Decision

A [Decision](CoreConcepts/Decision.md) compares the score and applies a final action for one direction. Place specific Decisions before general ones and verify that each action is valid for requests or responses.

## Create and Deploy a Defender

In the Defender form:

1. Choose a stable name.
2. Select an unused proxy port.
3. Configure the backend URL and required environment variables.
4. Attach a Guard if only specific Users may operate this Defender.
5. Apply Principles in `passed` state.
6. Implement Decisions in the correct direction.
7. Save and run deployment.

Manager creates a job and Worker calls [Orchestrator](Orchestrator-Guide.md). Monitor `deployment_status`, `deployment_details`, and logs. `successful` describes the deployment result, while `status` describes runtime health.

## Update a Running Policy

When changing a shared Target, Engine, Action, or Rule:

1. Identify all dependent Principles.
2. Validate those Principles again.
3. Find every Defender applying them.
4. Redeploy or synchronize through the current workflow.
5. Send a test request and inspect reports/logs.

Do not change a production policy without first observing it through logging or reporting.

## Investigation

Read data in this order:

1. [Report](CoreConcepts/Report.md): which request, Rule, and value matched.
2. Defender logs: how far execution progressed and which error occurred.
3. Deployment details: whether image, network, and environment are correct.
4. [Timeline](CoreConcepts/Timeline.md): who recently changed the policy or Defender.

## Sensitive Operations

User and Key administration, Defender deployment/cancellation, and blocking Decision changes require distinct permissions. Read [Security](Security.md) before assigning production access.
