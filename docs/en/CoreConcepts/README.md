# Core Concepts

This section explains the objects used in Manager and Defender. If Defly is new to you, follow this order instead of starting from configuration forms.

## Administration and Access

1. [User](User.md): sign-in account and configuration owner.
2. [Group](Group.md): collection of Users or API Keys for centralized permissions.
3. [Permission](Permission.md): authorization to act on Manager resources.
4. [Guard](Guard.md): per-Defender operator boundary for protected actions.
5. [Key](Key.md): credentials for API integrations.
6. [Label](Label.md): metadata for resource classification.

## WAF Policy

A policy is built from data toward actions:

1. [Wordlist](Wordlist.md) and [Pattern](Pattern.md) provide reusable data.
2. [Target](Target.md) selects a data location in the HTTP lifecycle.
3. [Engine](Engine.md) normalizes or changes the Target value's datatype.
4. [Rule](Rule.md) compares the Target value with configured data.
5. [Action](Action.md) defines what happens when a condition is true.
6. [Principle](Principle.md) combines Rules with AND in one phase.
7. [Decision](Decision.md) uses the total score for a final request or response verdict.
8. [Defender](Defender.md) receives and enforces these Principles and Decisions.

## Monitoring and Investigation

- [Report](Report.md) stores evidence from a firewall event.
- [Timeline](Timeline.md) stores Manager administration history.

They serve different purposes: Report describes runtime traffic, while Timeline records who changed configuration.
