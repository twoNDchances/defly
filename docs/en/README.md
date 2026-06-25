# Defly Documentation

Defly separates policy management, deployment coordination, and traffic enforcement
across Manager, Orchestrator, and Defender. Start with the path that matches your
task; each subject has one authoritative page.

## First Use

1. [Overview](Overview.md): product purpose and service boundaries.
2. [Installation](Installation.md): install with Docker Compose or from source.
3. [Getting Started](Getting-Started.md): build and test the first policy after installation.

## Authoritative References

| Subject | Responsible document |
| --- | --- |
| Service ownership and data flow | [Architecture](Architecture.md) |
| Configuration strategy and cross-service contracts | [Configuration](Configuration.md) |
| Variable names, defaults, and validation rules | [Environment Variables](Environment-Variables.md) |
| Model meaning and policy semantics | [Core Concepts](CoreConcepts/README.md) |
| Manager UI workflows | [Manager Guide](Manager-Guide.md) |
| Docker deployment coordination | [Orchestrator Guide](Orchestrator-Guide.md) |
| WAF request/response execution | [Defender Guide](Defender-Guide.md) |
| HTTP endpoints and payload contracts | [API Reference](API-Reference.md) |
| Runtime procedures | [Operations](Operations.md) |
| Trust boundaries and production controls | [Security](Security.md) |
| Symptom-based diagnosis | [Troubleshooting](Troubleshooting.md) |
| Contributor workflow | [Development](Development.md) |

Pages outside their responsibility provide only enough context to follow the workflow
and link back to the authoritative document instead of duplicating its tables or rules.
