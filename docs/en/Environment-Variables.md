# Environment Variables

This page documents Defly environment variables in four scopes:

1. The root `.env`, read by Docker Compose before services are created.
2. `manager/.env`, used when Manager runs directly.
3. `orchestrator/.env`, used when Orchestrator runs directly.
4. Defender variables, stored in a Defender record or passed directly to the program.

Do not treat two variables as interchangeable only because they carry the same value. Docker Compose may rename variables as it passes them into containers. For example, root `DB_USERNAME` becomes `DB_USER` in Orchestrator and `DATABASE_USER` in Defender.

## Value Conventions

- Boolean values use `true` or `false`.
- Laravel null values must be written as `null`.
- Lists of IPs, hosts, or networks are comma-separated and should not contain unnecessary spaces.
- API paths are relative and have no leading or trailing `/` unless stated otherwise.
- Passwords, application keys, and API keys in example files are placeholders. Replace them before production use.
- After changing Laravel `.env`, clear or rebuild the configuration cache. With Compose, run `docker compose config` to inspect final values before restarting services.

## Variables You Need to Reconfigure

After copying `.env.example` to `.env`, do not keep every example value unchanged. This section identifies what to edit first without requiring you to read every reference table.

### Required Before Production Use

| Variable | Why It Must Change | Recommended Value |
| --- | --- | --- |
| `MARIADB_ROOT_PASSWORD` | The example protects the MariaDB administrator account. | A strong, unique password stored in a secret manager. |
| `DB_PASSWORD` | Manager, Orchestrator, and Defender use it to access application data. | A strong application password distinct from the `root` password. |
| `ORCHESTRATOR_PASSWORD` | Protects an API with Docker control privileges. | A strong random password matching Orchestrator `SERVER_PASSWORD`. |
| `APP_KEY` | Encrypts Laravel cookies, sessions, and data. A process-local generated key is not persistent. | Output from `php artisan key:generate --show`, kept stable after launch. |
| `USER_EMAIL` | The bootstrap administrator should not use the example address. | The initial administrator's real email. |
| `USER_PASSWORD` | `random` is suitable for bootstrap only when retrieved and stored securely; never use a fixed example password. | A strong password, or keep `random` and retrieve it once from `credentials.txt`. |
| `APP_URL` | Affects generated links, cookies, and hostname behavior. | The real Manager HTTPS URL, such as `https://manager.example.com`. |

After changing database or Orchestrator credentials, update both sides according to [Cross-service Mapping](#cross-service-mapping).

### Change for Your Infrastructure

| Variable | When to Change It |
| --- | --- |
| `COMPOSE_PROJECT_NAME` | Change before first startup for a project name other than `defly`; changing it later creates a new network/volume set. |
| `MANAGER_IMAGE`, `ORCHESTRATOR_IMAGE`, `SERVER_DEFENDER_IMAGE` | When using your own registry, namespace, or version tags. `SERVER_DEFENDER_IMAGE` must exist on Orchestrator's Docker host. |
| `MANAGER_HTTP_PORT`, `MANAGER_HTTPS_PORT` | When ports `80`/`443` are occupied or Manager runs behind a reverse proxy. |
| `MANAGER_TLS_COMMON_NAME` | When the generated self-signed certificate must match a domain/IP other than `localhost`. Configure the external TLS termination point when certificates are managed elsewhere. |
| `ORCHESTRATOR_ALLOWED_HOSTS` | Add only the domains/IPs used to reach Orchestrator; avoid `*` in production unless explicitly required. |
| `ORCHESTRATOR_ALLOWED_CLIENTS` | Must include the real Orchestrator callers. Default Compose requires `worker`; manual deployments may use another host/IP. |
| `TIMEZONE`, `LANGUAGE_CODE` | When the system uses another timezone or language. Consistent timezones simplify Timeline, Report, and log correlation. |
| `SERVER_DEFENDER_TLS_VOLUME` | Only when intentionally renaming TLS storage and updating both Compose and Orchestrator. |
| `WORKER_TIMEOUT`, `WORKER_TRIES`, `WORKER_MAX_TIME` | When image retrieval/deployment exceeds defaults or needs another retry policy. |

### When Using Resend

Compose defaults to `MAIL_MAILER=resend`. To send verification or notification mail through Resend, configure:

| Variable | Requirement |
| --- | --- |
| `MAIL_MAILER` | Keep `resend`. |
| `RESEND_API_KEY` | Required; use a real Resend API key and never commit it. |
| `MAIL_FROM_ADDRESS` | Address on a domain verified by Resend. `onboarding@resend.dev` is suitable only for limited testing. |
| `MAIL_FROM_NAME` | Sender name shown to recipients. |

`RESEND_DOMAIN`, `RESEND_PATH`, `RESEND_WEBHOOK_SECRET`, and `RESEND_WEBHOOK_TOLERANCE` are present in the example but are not currently read directly by application code. They are not required merely to send mail. If real delivery is not needed yet, use `MAIL_MAILER=log` to write messages to logs instead of calling Resend.

### When Creating a Defender

Review at least these variables on every Defender record in Manager:

| Variable | Requirement |
| --- | --- |
| `PROXY_BACKEND_URL` | Must point to a backend reachable **from the Defender container**; do not use `localhost` when the backend is in another container/host. |
| `DATABASE_HOST`, `DATABASE_NAME`, `DATABASE_USER`, `DATABASE_PASS` | Must use the same database as Manager. Manager pre-fills its current connection, but verify it outside Compose. |
| `SERVER_SECURITY_USERNAME`, `SERVER_SECURITY_PASSWORD` | Replace example Basic Auth credentials and ensure Manager uses matching values for control API calls. |
| `SERVER_SECURITY_MANAGER` | Default Compose uses `worker`; manual execution must use the actual caller host/IP. |
| `PROXY_TRUSTED_ENABLE`, `PROXY_TRUSTED_LIST` | Enable only behind known trusted proxies with a correctly restricted IP/CIDR list. |
| `PROXY_SEVERITY_*`, `PROXY_VIOLATION_LEVEL`, `PROXY_VIOLATION_SCORE` | Keep defaults initially; change only after designing score accumulation and Decision thresholds. |

Orchestrator overrides `DEFENDER_NAME` and `PROXY_PORT` from the Defender record during deployment, so they do not need manual edits inside the environment object.

### Do Not Use TLS Skips as a Permanent Fix

Use `ORCHESTRATOR_TLS_SKIP_VERIFY=true` and `DEFENDER_SERVER_TLS_SKIP_VERIFY=true` only temporarily in development to isolate certificate problems. Production should keep both `false`, provide correct certificates, and fix hostname/trust paths instead of disabling verification.

## Root Docker Compose `.env`

Create the root `.env` from `.env.example`. Values in this section are interpolated by Docker Compose; containers receive the variable names declared in `docker-compose.yml`.

### Project, Images, and MariaDB

| Variable | Default | Meaning |
| --- | --- | --- |
| `COMPOSE_PROJECT_NAME` | `defly` | Prefix for Compose containers, networks, volumes, and labels. Changing it after first startup creates a separate Compose project. |
| `MANAGER_IMAGE` | `defly-manager:latest` | Image shared by `manager` and `worker`. |
| `ORCHESTRATOR_IMAGE` | `defly-orchestrator:latest` | Orchestrator image. |
| `SERVER_DEFENDER_IMAGE` | `defly-defender:latest` | Image Orchestrator uses for dynamic Defenders; it must exist on the Docker host. |
| `MARIADB_VERSION` | `11.4` | MariaDB image tag. |
| `MARIADB_ROOT_PASSWORD` | `defly_root_secret` | MariaDB `root` password. Use it only for database administration. |
| `DB_DATABASE` | `defly_manager` | Shared application database name. |
| `DB_USERNAME` | `defly` | MariaDB application user. |
| `DB_PASSWORD` | `defly_secret` | Application database password. |

Compose fixes `DB_HOST=mariadb` and `DB_PORT=3306` inside the relevant containers.

### Manager and Worker

| Variable | Default | Meaning |
| --- | --- | --- |
| `APP_NAME` | `Defly Manager` | Laravel application name and default mail sender name. |
| `APP_ENV` | `production` | Laravel environment name. |
| `APP_KEY` | empty | Laravel encryption key. Production requires a stable value generated with `php artisan key:generate --show`. |
| `APP_DEBUG` | `false` | Enables debug output. Never enable it in production. |
| `APP_URL` | `https://localhost` | Base URL used for links and, when needed, the Manager certificate name. |
| `APP_LOCALE` | `vi` | Default Manager locale. |
| `APP_FALLBACK_LOCALE` | `en` | Fallback locale for missing translations. |
| `APP_FAKER_LOCALE` | `en_US` | Locale for generated development/test data. |
| `MANAGER_HTTP_PORT` | `80` | Manager HTTP port published on the Docker host. |
| `MANAGER_HTTPS_PORT` | `443` | Manager HTTPS port published on the Docker host. |
| `MANAGER_TLS_COMMON_NAME` | `localhost` | Common name and SAN for Manager's generated self-signed certificate. |
| `MANAGER_RUN_MIGRATIONS` | `true` | Runs migrations when the Manager container starts. |
| `MANAGER_RUN_SEEDERS` | `true` | Runs seeders when the Manager container starts. |
| `MANAGER_RUN_OPTIMIZE` | `true` | Runs `php artisan optimize` during startup. |
| `GENERATE_APP_KEY` | `true` | Generates a process-local key if `APP_KEY` is empty. This temporary key is unsuitable for production. |
| `WORKER_TRIES` | `3` | Maximum attempts for a failed Worker job. |
| `WORKER_TIMEOUT` | `90` | Maximum seconds per Worker job. |
| `WORKER_MAX_TIME` | `3600` | Total seconds before Worker restarts itself. |

### Laravel Logging and State

| Variable | Default | Meaning |
| --- | --- | --- |
| `LOG_CHANNEL` | `stack` | Primary log channel. |
| `LOG_STACK` | `single` | Comma-separated channels included in `stack`. |
| `LOG_DEPRECATIONS_CHANNEL` | `null` | Channel for deprecation warnings. |
| `LOG_LEVEL` | `debug` | Lowest recorded log level. |
| `SESSION_DRIVER` | `database` | Session storage driver. |
| `SESSION_LIFETIME` | `120` | Session lifetime in minutes. |
| `SESSION_ENCRYPT` | `false` | Encrypts all session data before storage. |
| `SESSION_DOMAIN` | `null` | Session cookie domain; `null` uses the current host. |
| `BROADCAST_CONNECTION` | `log` | Event broadcasting connection. |
| `FILESYSTEM_DISK` | `local` | Default Laravel filesystem disk. |
| `QUEUE_CONNECTION` | `database` | Queue connection used by Worker. |
| `CACHE_STORE` | `database` | Cache store. |

### Mail and Resend

| Variable | Default | Meaning |
| --- | --- | --- |
| `MAIL_MAILER` | `resend` | Default mailer; the standard stack uses `MAIL_MAILER=resend`. |
| `MAIL_SCHEME` | `null` | SMTP connection scheme. |
| `MAIL_HOST` | `127.0.0.1` | SMTP host; unused by the Resend mailer. |
| `MAIL_PORT` | `2525` | SMTP port. |
| `MAIL_USERNAME` | `null` | SMTP username. |
| `MAIL_PASSWORD` | `null` | SMTP password. |
| `MAIL_FROM_ADDRESS` | `onboarding@resend.dev` | Default sender address. Production should use a verified domain. |
| `MAIL_FROM_NAME` | `Defly Manager` | Default sender name. |
| `RESEND_API_KEY` | empty | Resend API key; required to send through Resend. |
| `RESEND_DOMAIN` | `null` | Intended Resend domain. Current application code does not read it directly. |
| `RESEND_PATH` | `resend` | Intended Resend route segment. Current application code does not read it directly. |
| `RESEND_WEBHOOK_SECRET` | empty | Intended Resend webhook verification secret. Current application code does not read it directly. |
| `RESEND_WEBHOOK_TOLERANCE` | `300` | Intended webhook timestamp tolerance in seconds. Current application code does not read it directly. |

### Bootstrap User and UI

| Variable | Default | Meaning |
| --- | --- | --- |
| `USER_NAME` | `root` | Name of the User created by the seeder. |
| `USER_EMAIL` | `root@defly.2ndproject.site` | Sign-in email for the bootstrap User. |
| `USER_PASSWORD` | `random` | Bootstrap User password. Replace it with a strong, stable secret. |
| `TOKEN_LOCATION` | `header` | Where Manager API reads a Key: `header` or `body`; invalid values fall back to `header`. |
| `TOKEN_KEY_NAME` | `X-Token-Key` | Header or body field containing the Key. |
| `USER_AGENT` | `Defly/Manager` | `User-Agent` sent by Manager to other services. |
| `TIMEZONE` | `Asia/Ho_Chi_Minh` | Manager timezone; Compose also passes it to Orchestrator as `TIME_ZONE`. |
| `API_PREFIX` | `v1` | Manager API path prefix, normalized to a lowercase slug. |
| `GUI_PREFIX` | `defly-manager` | Filament UI path prefix. |
| `THEME_COLOR` | `violet` | Filament color name; unknown values fall back to `violet`. |

### Orchestrator in Compose

| Variable | Default | Meaning |
| --- | --- | --- |
| `ORCHESTRATOR_SECRET_KEY_FILE` | `storage/secret/key.txt` | File containing Django `SECRET_KEY`; the entrypoint creates it when absent. |
| `ORCHESTRATOR_ALLOWED_HOSTS` | `127.0.0.1,localhost,manager,orchestrator` | Host values accepted by Django. |
| `ORCHESTRATOR_ALLOWED_CLIENTS` | `manager,worker` | Source names or addresses allowed to call Orchestrator. Worker performs deployment jobs in Compose. |
| `ORCHESTRATOR_PATH_PREFIX` | `api/v1` | API prefix passed as `SERVER_PATH_PREFIX`. |
| `ORCHESTRATOR_PATH_DEPLOYMENT` | `deployments` | Deployment resource path passed as `SERVER_PATH_DEPLOYMENT`. |
| `ORCHESTRATOR_METHOD_DEPLOY` | `post` | Defender deployment method. |
| `ORCHESTRATOR_METHOD_FOLLOW` | `get` | Defender log-following method. |
| `ORCHESTRATOR_METHOD_CANCEL` | `delete` | Defender cancellation method. All three methods on the same path must differ. |
| `ORCHESTRATOR_USERNAME` | `defly-orchestrator` | Basic Auth username shared by Manager/Worker and Orchestrator. |
| `ORCHESTRATOR_PASSWORD` | `P@55w0rd` | Basic Auth password. Replace it in production. |
| `ORCHESTRATOR_TLS_SKIP_VERIFY` | `false` | When `true`, Manager skips Orchestrator certificate verification. Use only temporarily in development. |
| `ORCHESTRATOR_EMAIL_HEADER_KEY` | `X-Executor` | Header carrying the executor User email. Both sides must use the same name. |
| `LANGUAGE_CODE` | `vi-vn` | Django language. Compose supports it even though the root example does not currently declare it. |

### Deployed Defenders

| Variable | Default | Meaning |
| --- | --- | --- |
| `SERVER_DEFENDER_TLS_VOLUME` | `defender_tls` | Defender TLS volume key. The actual name is `${COMPOSE_PROJECT_NAME}_${SERVER_DEFENDER_TLS_VOLUME}`. |
| `DEFENDER_SERVER_TLS_SKIP_VERIFY` | `false` | When `true`, Manager skips Defender certificate verification. |

Detailed Defender variables do not come directly from the root `.env`. They are configured on the Defender record and passed to its container by Orchestrator.

## `manager/.env`

For manual execution, copy `manager/.env.example` to `manager/.env`. Variables also listed above keep the same meaning. `DB_HOST`, `DB_PORT`, and service URLs must be reachable from the actual Manager process.

### Application and Database

| Variable | Example Default | Meaning |
| --- | --- | --- |
| `APP_MAINTENANCE_DRIVER` | `file` | Stores Laravel maintenance state. |
| `APP_MAINTENANCE_STORE` | `database` | Shared store for maintenance mode; commented in the example. |
| `PHP_CLI_SERVER_WORKERS` | `4` | PHP CLI development server process count; commented in the example. |
| `BCRYPT_ROUNDS` | `12` | Intended bcrypt cost. Manager currently has no `config/hashing.php`, so application code does not read it directly. |
| `DB_CONNECTION` | `mysql` | Laravel database driver. |
| `DB_HOST` | `127.0.0.1` | Database host. |
| `DB_PORT` | `3306` | Database port. |
| `DB_DATABASE` | `defly_manager` | Database name. |
| `DB_USERNAME` | `root` | Database user. |
| `DB_PASSWORD` | empty | Database password. |

### Sessions, Cache, and Files

| Variable | Example Default | Meaning |
| --- | --- | --- |
| `SESSION_PATH` | `/` | Session cookie path scope. |
| `CACHE_PREFIX` | derived from `APP_NAME` | Cache key prefix; commented in the example. |
| `MEMCACHED_HOST` | `127.0.0.1` | Memcached host when `CACHE_STORE=memcached`. |
| `AWS_ACCESS_KEY_ID` | empty | AWS access key for S3, SQS, or DynamoDB. |
| `AWS_SECRET_ACCESS_KEY` | empty | AWS secret key. |
| `AWS_DEFAULT_REGION` | `us-east-1` | AWS region. |
| `AWS_BUCKET` | empty | S3 bucket name. |
| `AWS_USE_PATH_STYLE_ENDPOINT` | `false` | Enables path-style URLs for S3-compatible services. |
| `VITE_APP_NAME` | `${APP_NAME}` | Intended frontend application name. Current frontend code does not read it directly. |

### Orchestrator and Defender Connections

| Variable | Example Default | Meaning |
| --- | --- | --- |
| `ORCHESTRATOR_BASE_URL` | `https://orchestrator:8000` | Full Orchestrator URL. Outside Compose, use a reachable address such as `https://127.0.0.1:8000`. |
| `ORCHESTRATOR_TIMEOUT` | `90` | Maximum seconds Manager waits for an Orchestrator HTTP response. Increase it only when deployment, cancellation, or log-following calls need more time. |
| `ORCHESTRATOR_TLS_CERT_FILE` | `storage/tls/orchestrator/orchestrator.crt` | Certificate used to verify Orchestrator when TLS verification is enabled. |
| `DEFENDER_SERVER_TLS_DIRECTORY` | `storage/tls/defenders` | Defender certificate directory; Manager reads `<defender-name>.crt`. |

`ORCHESTRATOR_PATH_*`, `ORCHESTRATOR_METHOD_*`, `ORCHESTRATOR_USERNAME`, `ORCHESTRATOR_PASSWORD`, and `ORCHESTRATOR_EMAIL_HEADER_KEY` must match Orchestrator's `SERVER_*` values. `DEFENDER_SERVER_TLS_SKIP_VERIFY` controls only Manager's verification behavior.

### Manager Container Startup Variables

`manager/entrypoint.sh` reads these variables. Compose maps external `MANAGER_RUN_*` names to internal `RUN_*` names.

| Internal Variable | Default | Meaning |
| --- | --- | --- |
| `RUN_MIGRATIONS` | `true` | Runs migrations before Apache starts. Worker overrides it to `false`. |
| `RUN_SEEDERS` | `true` | Runs seeders. Worker overrides it to `false`. |
| `RUN_OPTIMIZE` | `true` | Builds Laravel optimization caches. Worker overrides it to `false`. |
| `FIX_PERMISSIONS` | `true` | Changes ownership of `storage` and `bootstrap/cache` to `www-data`. |
| `GENERATE_APP_KEY` | `true` | Generates a key when `APP_KEY` is empty. |
| `TLS_COMMON_NAME` | `SERVER_NAME`, then `APP_URL`, then `localhost` | Name used for the self-signed Apache certificate. |
| `SERVER_NAME` | empty | Apache server name and fallback for `TLS_COMMON_NAME`. |
| `TLS_DAYS` | `3650` | Validity period of the self-signed Apache certificate. |
| `APACHE_SERVER_NAME` | generated | Output created by the entrypoint for Apache; normally not set manually. |

### Advanced Laravel Variables

These variables are supported by `manager/config/*.php` but are not all present in `manager/.env.example`. Set them only when replacing a default driver.

| Group | Variables | Meaning |
| --- | --- | --- |
| Application keys | `APP_PREVIOUS_KEYS` | Comma-separated old keys used to decrypt data encrypted with a previous key. |
| Authentication | `AUTH_GUARD`, `AUTH_PASSWORD_BROKER`, `AUTH_MODEL`, `AUTH_PASSWORD_RESET_TOKEN_TABLE`, `AUTH_PASSWORD_TIMEOUT` | Guard, broker, User class, reset-token table, and password confirmation timeout. |
| Database | `DB_URL`, `DB_SOCKET`, `DB_CHARSET`, `DB_COLLATION`, `DB_FOREIGN_KEYS`, `DB_SSLMODE`, `MYSQL_ATTR_SSL_CA` | Connection URL, socket, charset, collation, SQLite foreign keys, PostgreSQL SSL mode, and MySQL CA. |
| Sessions | `SESSION_EXPIRE_ON_CLOSE`, `SESSION_CONNECTION`, `SESSION_TABLE`, `SESSION_STORE`, `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE`, `SESSION_PARTITIONED_COOKIE` | Session lifetime, connection, table, store, and cookie security attributes. |
| Database cache | `DB_CACHE_CONNECTION`, `DB_CACHE_TABLE`, `DB_CACHE_LOCK_CONNECTION`, `DB_CACHE_LOCK_TABLE` | Connections and tables for cache data and locks. |
| Memcached | `MEMCACHED_PERSISTENT_ID`, `MEMCACHED_USERNAME`, `MEMCACHED_PASSWORD`, `MEMCACHED_PORT` | Advanced Memcached connection values. |
| DynamoDB | `DYNAMODB_CACHE_TABLE`, `DYNAMODB_ENDPOINT` | DynamoDB cache table and endpoint. |
| Redis | `REDIS_CLIENT`, `REDIS_CLUSTER`, `REDIS_PREFIX`, `REDIS_PERSISTENT`, `REDIS_URL`, `REDIS_HOST`, `REDIS_USERNAME`, `REDIS_PASSWORD`, `REDIS_PORT`, `REDIS_DB`, `REDIS_CACHE_DB`, `REDIS_MAX_RETRIES`, `REDIS_BACKOFF_ALGORITHM`, `REDIS_BACKOFF_BASE`, `REDIS_BACKOFF_CAP`, `REDIS_CACHE_CONNECTION`, `REDIS_CACHE_LOCK_CONNECTION` | Redis client, cluster, key prefix, connections, databases, and retry strategy. |
| Database queue | `DB_QUEUE_CONNECTION`, `DB_QUEUE_TABLE`, `DB_QUEUE`, `DB_QUEUE_RETRY_AFTER` | Queue connection, table, name, and retry delay. |
| Beanstalkd | `BEANSTALKD_QUEUE_HOST`, `BEANSTALKD_QUEUE`, `BEANSTALKD_QUEUE_RETRY_AFTER` | Beanstalkd queue connection. |
| SQS | `SQS_PREFIX`, `SQS_QUEUE`, `SQS_SUFFIX` | SQS URL, queue name, and suffix. |
| Redis queue | `REDIS_QUEUE_CONNECTION`, `REDIS_QUEUE`, `REDIS_QUEUE_RETRY_AFTER` | Redis queue connection, name, and retry delay. |
| Failed jobs | `QUEUE_FAILED_DRIVER` | Failed-job storage driver; Laravel defaults to `database-uuids`. |
| Mail | `MAIL_URL`, `MAIL_EHLO_DOMAIN`, `MAIL_SENDMAIL_PATH`, `MAIL_LOG_CHANNEL` | SMTP URL, EHLO domain, sendmail command, and mail log channel. |
| Mail services | `POSTMARK_API_KEY` | Postmark API key. |
| Logging | `LOG_DEPRECATIONS_TRACE`, `LOG_DAILY_DAYS`, `LOG_SLACK_WEBHOOK_URL`, `LOG_SLACK_USERNAME`, `LOG_SLACK_EMOJI`, `LOG_PAPERTRAIL_HANDLER`, `PAPERTRAIL_URL`, `PAPERTRAIL_PORT`, `LOG_STDERR_FORMATTER`, `LOG_SYSLOG_FACILITY` | Deprecation traces, retention, Slack, Papertrail, stderr formatting, and syslog facility. |
| Slack | `SLACK_BOT_USER_OAUTH_TOKEN`, `SLACK_BOT_USER_DEFAULT_CHANNEL` | Bot token and default Slack channel. |
| S3 | `AWS_URL`, `AWS_ENDPOINT` | Public S3 URL and S3-compatible endpoint. |
| Livewire | `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK` | Disk for temporary Livewire uploads. |

`DB_ENCRYPT`, `DB_TRUST_SERVER_CERTIFICATE`, `POSTMARK_MESSAGE_STREAM_ID`, `VITE_PUSHER_APP_CLUSTER`, `VITE_PUSHER_APP_KEY`, `VITE_PUSHER_HOST`, `VITE_PUSHER_PORT`, and `VITE_PUSHER_SCHEME` occur only in commented configuration and have no effect until that code is enabled.

## `orchestrator/.env`

Orchestrator reads this file with `django-environ`. Defaults below come from source code and may differ from example values.

### Django and Database

| Variable | Source Default | Meaning |
| --- | --- | --- |
| `SECRET_KEY_FILE` | `storage/secret/key.txt` | File containing Django `SECRET_KEY`. It must exist and be non-empty; the container entrypoint or `manage.py` bootstrap command can create it. |
| `ALLOWED_HOSTS` | `*` | Hosts accepted by Django. The example uses a more restrictive list. |
| `DB_HOST` | `localhost` | MariaDB/MySQL host. |
| `DB_PORT` | `3306` | Database port. |
| `DB_USER` | `root` | Database user. |
| `DB_PASS` | empty | Database password. |
| `DB_NAME` | `defly` | Database name. The example and Compose use `defly_manager`. |
| `LANGUAGE_CODE` | `vi-vn` | Django language. |
| `TIME_ZONE` | `Asia/Ho_Chi_Minh` | Django timezone. |
| `USE_I18N` | `true` | Enables Django translations. |
| `USE_TZ` | `false` | Enables timezone-aware Django datetimes. |

### API and Docker

| Variable | Source Default | Meaning |
| --- | --- | --- |
| `SERVER_MANAGER` | `manager` | Sources allowed by middleware. Compose uses `manager,worker`. Must not be empty. |
| `SERVER_USERNAME` | `defly-orchestrator` | Basic Auth username; cannot be empty or contain `:`. |
| `SERVER_PASSWORD` | `P@55w0rd` | Basic Auth password; cannot be empty. |
| `SERVER_EMAIL_HEADER_KEY` | `X-Executor` | Header carrying the executor email. |
| `SERVER_PATH_PREFIX` | `api/v1` | API prefix without leading/trailing `/` or empty segments. |
| `SERVER_PATH_DEPLOYMENT` | `deployments` | Deployment path with the same validation. |
| `SERVER_METHOD_DEPLOY` | `post` | Deployment method. |
| `SERVER_METHOD_FOLLOW` | `get` | Log-following method. |
| `SERVER_METHOD_CANCEL` | `delete` | Cancellation method. All three must be different and one of `get`, `post`, `put`, `patch`, `delete`. |
| `SERVER_DEFENDER_IMAGE` | `defly-defender:latest` | Defender image present on the Docker host. |
| `SERVER_DEFENDER_TLS_VOLUME` | `defender_tls` | TLS volume key used for Defender containers. |
| `SERVER_DOCKER_BASE_URL` | `tcp://localhost:2375` | Development Docker endpoint. `configs.production` fixes it to `unix:///var/run/docker.sock`. |

### Orchestrator Entrypoint Variables

| Variable | Default | Meaning |
| --- | --- | --- |
| `ORCHESTRATOR_TLS_CERT_FILE` | `storage/tls/orchestrator.crt` | Uvicorn HTTPS certificate; generated by the entrypoint when absent. |
| `ORCHESTRATOR_TLS_KEY_FILE` | `storage/tls/orchestrator.key` | HTTPS private key path. |
| `DJANGO_SETTINGS_MODULE` | `configs.production` in the image | Selects Django settings. Outside the image, project tooling defaults to `configs.development`. |

## Defender Variables

Defender validates variable types, limits, and conditional requirements at startup. During Manager deployment, the record's `environment_variables` object is passed to Orchestrator, which always overrides `DEFENDER_NAME` and `PROXY_PORT` with record values.

### Common

| Variable | Default | Constraint and Meaning |
| --- | --- | --- |
| `DEFENDER_NAME` | `defender` | File-safe name used for certificates and resources. Orchestrator sets it during deployment. |
| `ABOUT_BANNER_ENABLE` | `true` | Shows the startup banner. |
| `ERROR_FILE_ENABLE` | `false` | Writes Defender errors to files. |
| `ERROR_DIRECTORY_PATH` | `storage/errors` | Valid directory path, required when `ERROR_FILE_ENABLE=true`. |
| `WORDLIST_ROOT` | `storage/wordlists` | Root directory for mounted Wordlist files. |

### Database and Doctor

| Variable | Default | Constraint and Meaning |
| --- | --- | --- |
| `DATABASE_HOST` | `127.0.0.1` | Policy/report database host without whitespace. Manager commonly supplies `mariadb` in Compose. |
| `DATABASE_PORT` | `3306` | Valid port from `1` to `65535`. |
| `DATABASE_NAME` | `defly_manager` | Database name without whitespace. |
| `DATABASE_USER` | `root` | Database user without whitespace. |
| `DATABASE_PASS` | empty | Database password. |
| `DOCTOR_INTERVAL_UNIT` | `minute` | Health-check unit: `second`, `minute`, or `hour`. |
| `DOCTOR_INTERVAL_COUNT` | `1` | Units between checks, `1` to `1000000`; at least `30` when the unit is `second`. |

### Defender Control Server

| Variable | Source Default | Constraint and Meaning |
| --- | --- | --- |
| `SERVER_HTTPS_ENABLE` | `true` | Enables HTTPS for the control API. |
| `SERVER_PORT` | `9947` | Control API port, `1` to `65535`. |
| `SERVER_CONTROLLER_PATH_PREFIX` | `api/v1` | Relative API prefix. |
| `SERVER_CONTROLLER_PATH_PRINCIPLES` | `principles` | Principle path; must differ from the Decision path. |
| `SERVER_CONTROLLER_METHOD_APPLY` | `put` | Apply-Principle method. |
| `SERVER_CONTROLLER_METHOD_REVOKE` | `delete` | Revoke-Principle method; must differ from apply. |
| `SERVER_CONTROLLER_PATH_DECISIONS` | `decisions` | Decision path. |
| `SERVER_CONTROLLER_METHOD_IMPLEMENT` | `put` | Implement-Decision method. |
| `SERVER_CONTROLLER_METHOD_SUSPEND` | `delete` | Suspend-Decision method; must differ from implement. |
| `SERVER_CONTROLLER_AUTHORIZATION_EMAIL` | `X-Executor` | Valid HTTP header name carrying the executor User email. |
| `SERVER_SECURITY_MANAGER` | `manager` | Host allowed to call the control API. Manager forms default to `worker`, while Defender's own fallback is `manager`. Cannot contain whitespace, `/`, `\`, or `:`. |
| `SERVER_SECURITY_USERNAME` | `defly-defender` | Basic Auth username, at least 4 characters. |
| `SERVER_SECURITY_PASSWORD` | `P@55w0rd` | Basic Auth password, at least 4 characters; replace it in production. |

Control methods accept only `post`, `put`, `patch`, or `delete`. Manager must use matching paths, methods, Basic Auth credentials, and executor header names.

### Defender Logging

| Variable | Default | Constraint and Meaning |
| --- | --- | --- |
| `SERVER_LOGGER_FILE_ENABLE` | `false` | Writes control API logs to a file. |
| `SERVER_LOGGER_FILE_PATH` | `storage/logs/server.log` | Writable log path, required when file logging is enabled. |
| `SERVER_LOGGER_FORMAT` | `[%time%] {%from%}: %status% %ip% %method% %path% %bytesSent% %bytesReceived% %error%` | Fiber logger template for the control API. |
| `SERVER_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Control API log timezone. |
| `PROXY_LOGGER_FILE_ENABLE` | `false` | Writes proxy logs to a file. |
| `PROXY_LOGGER_FILE_PATH` | `storage/logs/proxy.log` | Writable log path, required when file logging is enabled. |
| `PROXY_LOGGER_FORMAT` | same as `SERVER_LOGGER_FORMAT` | Fiber logger template for the proxy. |
| `PROXY_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Proxy log timezone. |

### Proxy and Violation Scores

| Variable | Default | Constraint and Meaning |
| --- | --- | --- |
| `PROXY_BACKEND_URL` | `http://localhost` | Backend URL receiving requests after WAF processing. |
| `PROXY_PORT` | `9948` | Proxy port, `1` to `65535`; Orchestrator overrides it from the Defender record. |
| `PROXY_TRUSTED_ENABLE` | `false` | Trusts client address information from an upstream proxy. |
| `PROXY_TRUSTED_LIST` | empty | Comma-separated IPs or CIDRs, required when `PROXY_TRUSTED_ENABLE=true`. |
| `PROXY_PRESERVE_HOST` | `true` | Preserves the original HTTP `Host` header when forwarding. |
| `PROXY_SEVERITY_INFO` | `1` | `INFO` score increment, `1` to `1000`. |
| `PROXY_SEVERITY_NOTICE` | `2` | `NOTICE` score increment, `1` to `1000`. |
| `PROXY_SEVERITY_WARNING` | `3` | `WARNING` score increment, `1` to `1000`. |
| `PROXY_SEVERITY_ERROR` | `4` | `ERROR` score increment, `1` to `1000`. |
| `PROXY_SEVERITY_CRITICAL` | `5` | `CRITICAL` score increment, `1` to `1000`. |
| `PROXY_SEVERITY_ALERT` | `6` | `ALERT` score increment, `1` to `1000`. |
| `PROXY_SEVERITY_EMERGENCY` | `7` | `EMERGENCY` score increment, `1` to `1000`. |
| `PROXY_VIOLATION_LEVEL` | `1` | Initial Principle level, `1` to `1000000`. |
| `PROXY_VIOLATION_SCORE` | `5` | Fallback threshold for a Decision whose `score = 0`, `5` to `100000`. |

## Cross-service Mapping

| Configuring Side | Receiving Side | Relationship |
| --- | --- | --- |
| `DB_DATABASE` | `DB_NAME`, `DATABASE_NAME` | Same database name. |
| `DB_USERNAME` | `DB_USER`, `DATABASE_USER` | Same database user. |
| `DB_PASSWORD` | `DB_PASS`, `DATABASE_PASS` | Same database password. |
| `ORCHESTRATOR_USERNAME` | Orchestrator `SERVER_USERNAME` | Manager/Worker -> Orchestrator Basic Auth. |
| `ORCHESTRATOR_PASSWORD` | Orchestrator `SERVER_PASSWORD` | Matching password. |
| `ORCHESTRATOR_EMAIL_HEADER_KEY` | `SERVER_EMAIL_HEADER_KEY` | Executor header name. |
| `ORCHESTRATOR_PATH_PREFIX` | `SERVER_PATH_PREFIX` | Orchestrator API prefix. |
| `ORCHESTRATOR_PATH_DEPLOYMENT` | `SERVER_PATH_DEPLOYMENT` | Deployment path. |
| `ORCHESTRATOR_METHOD_*` | `SERVER_METHOD_*` | Methods for deployment operations. |
| Manager Defender client settings | `SERVER_CONTROLLER_*`, `SERVER_SECURITY_*` | Defender control API paths, methods, and Basic Auth. |
| `SERVER_DEFENDER_IMAGE` | Orchestrator `SERVER_DEFENDER_IMAGE` | Deployed Defender image. |
| `SERVER_DEFENDER_TLS_VOLUME` | Compose `defender_tls` volume | Shared certificate storage. |

## System-managed Variables

- Docker sets `HOSTNAME` for the Orchestrator container; Orchestrator uses it to discover current Compose labels and networks.
- Orchestrator always overrides `DEFENDER_NAME` and `PROXY_PORT` when creating a Defender container.
- Compose fixes Manager/Orchestrator database addressing and uses `/var/run/docker.sock` for production Orchestrator.
- Compose fixes `ORCHESTRATOR_BASE_URL=https://orchestrator:8000` and in-container certificate paths.
- Worker inherits Manager's environment but disables migrations, seeders, and optimization at startup.

## Verification After Changes

1. Run `docker compose config` and inspect rendered values.
2. Ensure Manager/Orchestrator and Manager/Defender contract pairs match.
3. Never retain example secrets such as `P@55w0rd`, `defly_secret`, or `random` in production.
4. Restart the service that reads the variable; redeploy Defender when its record environment changes.
5. Check read/write permissions for keys, certificates, logs, errors, and Wordlists.
6. Inspect startup logs: Defender and Orchestrator reject many invalid values while loading configuration.

See also [Configuration](Configuration.md), [Installation](Installation.md), [Orchestrator Guide](Orchestrator-Guide.md), and [Defender](CoreConcepts/Defender.md).
