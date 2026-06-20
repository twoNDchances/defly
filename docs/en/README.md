# Defly Documentation

Defly is a multi-service system for managing and enforcing web application firewall (WAF) policies. It consists of [Manager](Manager-Guide.md), [Orchestrator](Orchestrator-Guide.md), and one or more [Defenders](CoreConcepts/Defender.md). This documentation is organized as a reading path from overview to operations.

## Getting Started

1. [Overview](Overview.md): what Defly solves and how its services cooperate.
2. [Getting Started](Getting-Started.md): run the system with Docker Compose and create your first Defender.
3. [Installation](Installation.md): install the full Compose stack or run each service manually.
4. [Configuration](Configuration.md): how configuration, databases, TLS, and Docker are organized.
5. [Environment Variables](Environment-Variables.md): every `.env` variable, default value, constraint, and cross-service mapping.
6. [Architecture](Architecture.md): service boundaries, data ownership, and the HTTP lifecycle.

## Core Concepts

Read the [core concepts index](CoreConcepts/README.md) before building a policy. The recommended WAF reading order is:

1. [Wordlist](CoreConcepts/Wordlist.md) and [Pattern](CoreConcepts/Pattern.md)
2. [Target](CoreConcepts/Target.md)
3. [Engine](CoreConcepts/Engine.md)
4. [Rule](CoreConcepts/Rule.md)
5. [Action](CoreConcepts/Action.md)
6. [Principle](CoreConcepts/Principle.md)
7. [Decision](CoreConcepts/Decision.md)
8. [Defender](CoreConcepts/Defender.md)

Administration concepts include [User](CoreConcepts/User.md), [Group](CoreConcepts/Group.md), [Permission](CoreConcepts/Permission.md), [Key](CoreConcepts/Key.md), and [Label](CoreConcepts/Label.md).

## Using the System

- [Manager Guide](Manager-Guide.md)
- [Orchestrator Guide](Orchestrator-Guide.md)
- [Defender Guide](Defender-Guide.md)
- [API Reference](API-Reference.md)

## Operations and Development

- [Operations](Operations.md)
- [Security](Security.md)
- [Troubleshooting](Troubleshooting.md)
- [Development](Development.md)
- [Quick Reference](Reference.md)

For a quick trial, start with [Getting Started](Getting-Started.md). To build WAF policies, read the [core concepts](CoreConcepts/README.md) in order before opening their forms in Manager.
