# Defly

Defly is a workspace for managing, deploying, and running Defender services for
web application protection. It brings the management UI, deployment
orchestrator, and Defender runtime into one repository.

## Documentation

| Language | File |
| --- | --- |
| English | [README.en.md](docs/en/README.en.md) |
| Vietnamese | [README.vi.md](docs/vi/README.vi.md) |

## Services

- `manager`: Laravel and Filament management UI/API.
- `orchestrator`: Django ASGI service that controls Defender containers.
- `defender`: Go runtime that applies WAF rules and proxies protected traffic.

Use the language-specific README files for setup instructions.
