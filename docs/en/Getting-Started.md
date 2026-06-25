# Getting Started

This page begins after Defly is installed and healthy. For commands and prerequisites,
use [Installation](Installation.md); for service diagnostics, use
[Operations](Operations.md#layered-health-checks).

## 1. Sign In and Inspect the Dashboard

Open Manager, sign in with the bootstrap account, and confirm that database, queue,
Orchestrator, and Defender summaries do not report infrastructure failures. Change a
temporary bootstrap password before creating additional users.

## 2. Establish Access

Create one least-privilege operator role before onboarding more users, and use an API
Key rather than a shared password for automation. Follow the concrete Manager steps
in [Access Administration](Manager-Guide.md#access-administration); authorization
precedence belongs in the linked User, Group, Permission, and Key concept pages.

## 3. Build a Safe First Policy

Use [Build a Policy](Manager-Guide.md#build-a-policy) for the form sequence and
[Core Concepts](CoreConcepts/README.md) for model rules. For this first pass, choose
one narrow request signal, attach `log` or `report` rather than `deny`, validate the
Principle, and resolve every validation error. This produces evidence without risking
an accidental outage.

## 4. Create the First Defender

Follow [Create and Deploy a Defender](Manager-Guide.md#create-and-deploy-a-defender).
For the first instance, use a unique name, an unused proxy port, and a backend URL
reachable from inside its container. Wait for `deployment_status=successful` before
applying or implementing policy. The meaning of each state belongs in
[Defender](CoreConcepts/Defender.md).

## 5. Verify Behavior

Send test traffic through the Defender proxy, then verify in order:

- the backend receives allowed traffic;
- Defender logs have no transport/runtime error;
- the expected Rule matched;
- the expected Action produced a log or Report;
- score and Decision behavior match the test case.

Only change an Action to `deny` after positive, negative, malformed, and bypass cases
behave as expected. Use [Troubleshooting](Troubleshooting.md) by symptom when a layer
fails.
