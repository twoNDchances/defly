# Defender

A Defender is both a Manager configuration record and the WAF/reverse-proxy instance
created from that record. This page defines the model and its state invariants. Docker
creation belongs in the [Orchestrator Guide](../Orchestrator-Guide.md), runtime traffic
in the [Defender Guide](../Defender-Guide.md), and exact environment keys in
[Environment Variables](../Environment-Variables.md#defender-variables).

## Fields

| Field | Owner | Meaning |
| --- | --- | --- |
| `name` | User | Unique lowercase hyphenated identity used to correlate record, container, certificate, and control hostname |
| `proxy_port` | User | Host port published for protected traffic |
| `environment_variables` | User/system | Validated common, control-server, and proxy configuration stored as one JSON object |
| `description` | User | Administrative context |
| `deployment_status` | Deployment workflow | Progress of create/cancel work |
| `deployment_details` | Deployment workflow | Result or actionable failure details |
| `status` | Defender runtime | Doctor health (`normal`, `abnormal`, or unknown) |
| `details` | Defender runtime | Health evidence |
| `last_response_details` | Policy-control workflow | Latest apply/revoke/implement/suspend response |

Manager presents the environment as fixed groups. Users edit values, not arbitrary key
sets; system-managed values such as Defender identity and effective proxy port are
injected during deployment.

## Three Independent State Dimensions

Do not collapse these states into one “running” flag.

### Deployment State

| Value | Meaning |
| --- | --- |
| `pending` | Work was queued |
| `processing` | Worker/Orchestrator is executing it |
| `failed` | The deployment operation failed; inspect `deployment_details` |
| `successful` | The container was created successfully |
| `null` | No active deployment result |

A pending/processing record cannot be redeployed. A successful record must be canceled
before deletion.

### Runtime Health

`status` and `details` come from Defender's Doctor/runtime behavior. A successful
deployment may be unhealthy, and a healthy-looking database record is not proof that
the proxy/backend path works. Use layered checks from [Operations](../Operations.md).

### Policy Activation

Attachment and activation are separate:

| Relationship | Ordered pivot flag | Active when |
| --- | --- | --- |
| [Principle](Principle.md) | `is_applied` | Attached, validated, and successfully applied |
| [Decision](Decision.md) | `is_implemented` | Attached and successfully implemented |

Attaching only creates deployment intent. Manager's apply/revoke and
implement/suspend actions call the Defender control API; pivot flags change only after
a successful response. Revoke/suspend before detaching an active item.

## Policy Constraints

- Only a Principle with validation status `passed` may enter the normal apply flow.
- Apply/implement requires a successfully deployed Defender.
- Principles and Decisions retain relationship order.
- Referenced Principles/Decisions remain locked while attached to any Defender.
- Principle validation and every Defender lifecycle/policy-control action are manual
  Manager workflows.

`last_response_details` keeps the latest control response separately for Principle and
Decision operations so a deployment result is not confused with a policy result.

## Reports

A Defender owns many [Reports](Report.md). In this relationship,
`reports.created_by` identifies the Defender that observed the request rather than an
administrative User.

## Before Deployment

Verify the model-level inputs:

- unique, resolvable name;
- unused host proxy port;
- backend URL and database settings valid from the Defender network;
- validated Principles and intentional Decisions;
- environment values accepted by Manager validation.

Then use [Configuration](../Configuration.md) for cross-service agreement,
[Orchestrator Guide](../Orchestrator-Guide.md) for container behavior, and
[Troubleshooting](../Troubleshooting.md) for failures.
