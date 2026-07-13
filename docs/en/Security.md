# Security

Defly both manages security policy and controls Docker, so protect its control plane as carefully as WAF data.

## Trust Boundaries

| Boundary | Primary Mechanism |
| --- | --- |
| User -> Manager UI | Laravel/Filament authentication and [Permissions](CoreConcepts/Permission.md). |
| Client -> Manager API | Basic Auth and [Key](CoreConcepts/Key.md). |
| Worker -> Orchestrator | Basic Auth, caller allowlist, and TLS. |
| Manager -> Defender API | Internal authorization and TLS. |
| Orchestrator -> Docker | Highly privileged Docker socket or API. |
| Client -> Defender proxy | WAF policy and TLS/proxy configuration. |

## Users, Groups, and Permissions

Apply least privilege through [Groups](CoreConcepts/Group.md). Separate at least these roles:

- User and Key administration.
- Policy construction and validation.
- Policy application and Defender deployment.
- Viewing reports containing sensitive data.

Regularly review inactive Users and Group membership.

Use [Guards](CoreConcepts/Guard.md) when only specific operators should control a
specific Defender. Guard is intentionally narrower than Permission: the Defender owner
can still operate their own Defender, but other root Users or Users with `Defender:all`
must belong to an active matching Guard when the Defender is guarded.

## Credentials and Secrets

Never commit:

- Real `.env` files.
- Database or Orchestrator passwords.
- API tokens.
- TLS private keys.
- Django secret files.
- Production database or Report copies.

Manager API tokens are hashed at rest and cannot be recovered. Distribute each token once through a secure channel and revoke it when control is lost.

## TLS Between Services

In production, set:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY=false
DEFENDER_SERVER_TLS_SKIP_VERIFY=false
```

Manager must read the correct `.crt` certificate. Private keys belong only on their corresponding servers. Do not rely indefinitely on unmanaged self-signed certificates in a distributed system.

## Docker Daemon

The Docker daemon can create privileged containers, mount host filesystems, and read volumes. Treat Orchestrator as a highly privileged host component.

- Prefer a Unix socket on a controlled host.
- Never expose TCP `2375` publicly.
- If TCP is required, place it on a private network with appropriate TLS/mTLS.
- Restrict hosts allowed to call Orchestrator.
- Monitor unexpected containers and images.

## WAF Data and Privacy

[Reports](CoreConcepts/Report.md), logging Actions, and the `save` Decision may store HTTP headers, cookies, tokens, bodies, or personal data.

- Collect only what an investigation requires.
- Restrict access to Reports and storage volumes.
- Define retention and deletion procedures.
- Encrypt backups.
- Do not send sensitive data through the `request` Action to an untrusted destination.

## Policy Safety

An incorrect [Rule](CoreConcepts/Rule.md) or [Decision](CoreConcepts/Decision.md) can block legitimate traffic.

Recommended workflow:

1. Test Target and Engine with representative data.
2. Start with `log`, `report`, or `suspect` Actions.
3. Validate the [Principle](CoreConcepts/Principle.md).
4. Apply it in a staging environment.
5. Monitor false positives.
6. Enable blocking/cancellation only with a clear rollback criterion.

## Production Checklist

- `APP_DEBUG=false`.
- Strong secrets and passwords with a rotation schedule.
- TLS verification enabled between services.
- Docker API not exposed publicly.
- Database and volumes backed up.
- Reports/raw requests have retention limits.
- Users, Groups, and Permissions reviewed.
- Guard membership and expiration reviewed for production Defenders.
- Principles are `passed` and Decisions tested in both directions.
- A bypass or rollback path exists for false blocking.
