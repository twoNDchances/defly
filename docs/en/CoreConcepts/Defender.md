# Defender

A Defender is a WAF and reverse proxy protecting one backend. Its Manager record stores deployment configuration, environment variables, runtime status, and policy relationships; Orchestrator uses the record to create a Defender container.

## Main Fields

| Field | Required | Meaning |
| --- | --- | --- |
| `name` | Yes | Unique lowercase hyphen-separated name, up to 255 characters; also used as hostname/container name. |
| `proxy_port` | Yes | Published host and container proxy port, `1` to `65535`; default `9948`. |
| `environment_variables` | Yes | JSON object containing all common/server/proxy variables. |
| `status` | System | Runtime health: `normal` or `abnormal`. |
| `details` | System | JSON explaining health status. |
| `deployment_status` | System | Deployment state. |
| `deployment_details` | System | Deployment/cancellation result or error. |
| `last_response_details` | System | Latest policy apply/revoke/implement/suspend response. |
| `description` | No | Administration notes. |

Manager presents three fixed environment lists. Keys cannot be added, removed, or reordered; save merges them into one `environment_variables` object.

## Common Variables

| Variable | Default | Meaning/Constraint |
| --- | --- | --- |
| `ABOUT_BANNER_ENABLE` | `true` | Information banner; `true`/`false`. |
| `ERROR_FILE_ENABLE` | `false` | Writes errors to files. |
| `ERROR_DIRECTORY_PATH` | `storage/errors` | Error directory; must not end in `.` or `..`. |
| `DATABASE_HOST` | Manager DB, fallback `mariadb` | Database host without whitespace. |
| `DATABASE_PORT` | `3306` | Port `1..65535`. |
| `DATABASE_NAME` | Manager DB | Policy/report database. |
| `DATABASE_USER` | Manager DB | Database user. |
| `DATABASE_PASS` | Manager DB | Database password; may be empty. |
| `DOCTOR_INTERVAL_UNIT` | `minute` | `second`, `minute`, or `hour`. |
| `DOCTOR_INTERVAL_COUNT` | `1` | Integer `1..1000000`; at least `30` with `second`. |

Defender uses the database connection to load policy and write [Reports](Report.md). Doctor interval controls health-check frequency.

## Control Server Variables

Manager uses the control server to apply/revoke Principles and implement/suspend Decisions.

| Variable | Default | Meaning |
| --- | --- | --- |
| `SERVER_HTTPS_ENABLE` | `true` | Enables control API HTTPS. |
| `SERVER_LOGGER_FILE_ENABLE` | `false` | Enables server log file. |
| `SERVER_LOGGER_FILE_PATH` | `storage/logs/server.log` | Valid log path. |
| `SERVER_LOGGER_FORMAT` | Built-in template | Up to 2048 characters. |
| `SERVER_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Valid timezone. |
| `SERVER_PORT` | `9947` | Control port `1..65535`. |
| `SERVER_CONTROLLER_PATH_PREFIX` | `api/v1` | Path prefix without `.`/`..` segments. |
| `SERVER_CONTROLLER_PATH_PRINCIPLES` | `principles` | Principle endpoint. |
| `SERVER_CONTROLLER_METHOD_APPLY` | `put` | Apply method: `post`, `put`, `patch`, `delete`. |
| `SERVER_CONTROLLER_METHOD_REVOKE` | `delete` | Revoke method. |
| `SERVER_CONTROLLER_PATH_DECISIONS` | `decisions` | Decision endpoint. |
| `SERVER_CONTROLLER_METHOD_IMPLEMENT` | `put` | Implement method. |
| `SERVER_CONTROLLER_METHOD_SUSPEND` | `delete` | Suspend method. |
| `SERVER_CONTROLLER_AUTHORIZATION_EMAIL` | `X-Executor` | Header carrying executor email. |
| `SERVER_SECURITY_MANAGER` | `worker` | Trusted Manager identity/hostname. |
| `SERVER_SECURITY_USERNAME` | `defly-defender` | Basic Auth username, at least 4 characters. |
| `SERVER_SECURITY_PASSWORD` | `P@55w0rd` | Basic Auth password, at least 4 characters. |

Manager builds control URLs as:

```text
http(s)://<defender.name>:<SERVER_PORT>/<PATH_PREFIX>/<resource-path>
```

It sends Basic Auth and the executor header. With TLS verification enabled, Manager looks for `storage/tls/defenders/<defender-name>.crt` under current configuration.

## Proxy Variables

| Variable | Default | Meaning |
| --- | --- | --- |
| `PROXY_BACKEND_URL` | `http://localhost` | Protected backend URL. |
| `PROXY_LOGGER_FILE_ENABLE` | `false` | Enables proxy log file. |
| `PROXY_LOGGER_FILE_PATH` | `storage/logs/proxy.log` | Valid log path. |
| `PROXY_LOGGER_FORMAT` | Built-in template | Up to 2048 characters. |
| `PROXY_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Valid timezone. |
| `PROXY_PORT` | `9948` | Proxy port `1..65535`; Orchestrator overrides from record `proxy_port`. |
| `PROXY_TRUSTED_ENABLE` | `false` | Enables trusted-proxy list. |
| `PROXY_TRUSTED_LIST` | `null` | Comma-separated IP/CIDR list. |
| `PROXY_PRESERVE_HOST` | `true` | Preserves HTTP `Host` when forwarding. |
| `PROXY_SEVERITY_INFO` | `1` | `suspect(info)` score. |
| `PROXY_SEVERITY_NOTICE` | `2` | Notice score. |
| `PROXY_SEVERITY_WARNING` | `3` | Warning score. |
| `PROXY_SEVERITY_ERROR` | `4` | Error score. |
| `PROXY_SEVERITY_CRITICAL` | `5` | Critical score. |
| `PROXY_SEVERITY_ALERT` | `6` | Alert score. |
| `PROXY_SEVERITY_EMERGENCY` | `7` | Emergency score. |
| `PROXY_VIOLATION_LEVEL` | `1` | Initial [Principle](Principle.md) level. |
| `PROXY_VIOLATION_SCORE` | `5` | Baseline/fallback score; validation range `5..100000`. |

Severity scores are integers `1..1000`; violation level is `1..1000000`.

## Deployment Lifecycle

`deployment_status` values:

| Status | Meaning |
| --- | --- |
| `pending` | Deployment/cancellation queued. |
| `processing` | Job or Orchestrator is working. |
| `failed` | Request, Docker operation, or deployment failed. |
| `successful` | Container was created successfully. |

Deployment flow:

1. Manager sets `pending` and queues `DefenderDeployment`.
2. Job sets `processing` and calls Orchestrator.
3. Orchestrator loads the record, normalizes environment JSON, and verifies the image.
4. Existing same-name container is removed; log/error volumes are created; TLS volume must exist.
5. Orchestrator adds `DEFENDER_NAME`, overrides `PROXY_PORT`, publishes the port, and starts with `unless-stopped` restart policy.
6. Success sets `successful`; errors set `failed` with details/exception/logs.

Redeployment is blocked during `pending` or `processing`. A `successful` Defender cannot be deleted directly; cancel first.

## Docker Compose Relationship

Orchestrator reads labels and networks from its own container. A dynamic Defender:

- Joins every current Compose network, the first during creation and others afterward.
- Receives `com.docker.compose.project`, `service`, `config-hash`, `oneoff`, and project metadata when available.
- Uses Compose-project-prefixed volume names.

These labels make the Defender part of the current project so `docker compose down` can discover/remove it. Outside Compose, or without access to the current container through Docker API, only Defly labels are added and no project/network context is inherited.

## Cancel and Follow

Manager queues `cancel` only for a `successful` Defender. Orchestrator force-removes the named container. On success, Manager clears `status`, `details`, `deployment_status`, and `deployment_details`; log/error volumes remain.

`follow` retrieves container stdout/stderr and returns at most the latest 100 lines without changing deployment state.

## Health Status

`status` (`normal`/`abnormal`) and `details` describe Doctor/runtime health independently from deployment. A successfully deployed container may be unhealthy; a record without health data may have `status = null`.

Do not substitute `deployment_status` for a health check.

## Policy Management

Defender has two ordered relationships:

| Resource | Pivot Flag | Active When |
| --- | --- | --- |
| [Principle](Principle.md) | `is_applied` | Attached and successfully applied. |
| [Decision](Decision.md) | `is_implemented` | Attached and successfully implemented. |

### Principle

- Standard flow applies only `passed` Principles.
- Defender deployment must be `successful`.
- Apply/revoke run through queue and control API.
- Pivot flag changes only after a successful HTTP response.

### Decision

- Defender deployment must be `successful`.
- Implement/suspend run through queue and control API.
- Pivot flag changes only after a successful HTTP response.

`last_response_details` separately stores `principle` and `decision` branches with action, resource ID, requester email, HTTP status/response or exception, and response time.

Attachment creates only the relationship; apply/implement activates runtime behavior. Revoke/suspend before detaching an active resource.

## Report

A Defender owns many [Reports](Report.md) through `reports.created_by`. For Report, `created_by` points to Defender rather than User.

## Deployment Checklist

- Use a valid Defender name resolvable from Manager/container networks.
- Ensure `proxy_port` is free on the host.
- Use backend URL and database credentials valid inside the Docker network.
- Pre-create required TLS volume/certificates.
- Give Orchestrator Docker socket access and Compose labels/networks.
- Wait for `successful` before applying Principles or implementing Decisions.
- Distinguish health, deployment, and pivot policy state.
