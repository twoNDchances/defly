# Development

Defly is a monorepo containing Laravel, Django, and Go. A cross-service contract change must update source code, tests, `.env.example`, and documentation together.

## Setup

Run the full stack through Compose or each service through [Manual Installation](Installation.md#manual-installation). In both cases, all services use the schema owned by Manager migrations.

## Manager

```powershell
cd manager
php artisan test
npm run build
```

Common commands:

```powershell
php artisan migrate
php artisan db:seed
php artisan queue:work --tries=3
```

When changing a Filament form, update validators, data mapping, API request/controller behavior, and corresponding tests.

## Orchestrator

```powershell
cd orchestrator
uv run python manage.py check
uv run python -m ruff check .
uv run python manage.py test
```

Deployment tests should cover Docker arguments, Compose labels, networks, volumes, and failure cleanup.

## Defender

```powershell
cd defender
go test ./...
```

Firewall only:

```powershell
go test ./internal/firewall/...
```

Run `gofmt` on modified Go files.

## Changing the WAF Pipeline

Dependencies follow this order:

```text
Pattern/Wordlist -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

When adding or changing a component:

1. Update Manager enums, validation, and form/API behavior.
2. Update data mapping so configuration JSON has the same structure.
3. Update Defender to read that structure at runtime.
4. Add unit tests for datatypes, phases, and edge cases.
5. Add integration tests for HTTP transactions.
6. Confirm [Report](CoreConcepts/Report.md) still describes results correctly.
7. Update the corresponding concept page.

### Pattern

Every name in `PatternSeeder.php` needs a corresponding Defender extractor. Tests must confirm phase, type, and datatype.

### Engine

Manager and Defender must agree on input/output datatypes and optional/default parameters.

### Comparator

Adding a comparator requires changes to Manager enums and datatype selection, Defender comparison logic, and Report match values.

### Action and Decision

Define whether an Action stops the chain, runs synchronously or asynchronously, affects requests or responses, and which settings are required.

## Changing APIs or Environment Variables

When changing a path, method, HTTP header, or cross-service variable:

- Update caller and receiver.
- Update root and service `.env.example` files.
- Update contract tests.
- Update [Configuration](Configuration.md) and [API Reference](API-Reference.md).

## Code Generation

Do not manually edit generated files when the project provides a generator. For Ent and similar output, change the source schema, run generation, and review the resulting diff.

## Pre-submission Checklist

- Tests covering the change pass.
- Formatters and code checks have run.
- No secrets or runtime data appear in the diff.
- Migrations and seeds have a clear upgrade path.
- Manager/Orchestrator/Defender contracts remain synchronized.
- Markdown links are valid.
- New terminology is explained or linked to an earlier definition.
