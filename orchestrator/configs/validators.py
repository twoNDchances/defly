from pathlib import Path
from sys import argv as sys_argv

from django.core.exceptions import ImproperlyConfigured

ALLOWED_DEPLOY_METHODS = frozenset({"get", "post", "put", "patch", "delete"})
REGISTERED_ENDPOINT_METHODS: dict[str, dict[str, str]] = {}


def require_non_empty(name: str, value: str) -> str:
    normalized = value.strip()
    if not normalized:
        raise ImproperlyConfigured(f"{name} cannot be empty.")
    return normalized


def validate_path_value(name: str, value: str) -> str:
    normalized = require_non_empty(name, value)
    if normalized.startswith("/") or normalized.endswith("/"):
        raise ImproperlyConfigured(
            f"{name} must not start or end with '/'. Got: {normalized!r}"
        )

    segments = [segment.strip() for segment in normalized.split("/")]
    if any(not segment for segment in segments):
        raise ImproperlyConfigured(
            f"{name} contains an empty path segment. Got: {normalized!r}"
        )

    return "/".join(segments)


def validate_username(name: str, value: str) -> str:
    username = require_non_empty(name, value)
    if ":" in username:
        raise ImproperlyConfigured(f"{name} cannot contain ':'.")
    return username


def validate_http_method(name: str, value: str) -> str:
    method = require_non_empty(name, value).lower()
    if method not in ALLOWED_DEPLOY_METHODS:
        allowed_methods = ", ".join(sorted(ALLOWED_DEPLOY_METHODS))
        raise ImproperlyConfigured(
            f"{name} must be one of [{allowed_methods}]. Got: {method!r}"
        )
    return method


def validate_endpoint_method_registry(
    *, endpoint: str, registry: list[dict[str, str]]
) -> None:
    if not registry:
        raise ImproperlyConfigured(
            f"Method registry for endpoint {endpoint!r} cannot be empty."
        )

    seen_methods: dict[str, str] = {}
    for item in registry:
        setting_name = item.get("name", "").strip()
        method_value = item.get("method", "").strip().lower()

        if not setting_name:
            raise ImproperlyConfigured(
                f"Method registry for endpoint {endpoint!r} has an empty name."
            )
        if not method_value:
            raise ImproperlyConfigured(
                f"Method registry for endpoint {endpoint!r} has an empty method."
            )

        if method_value in seen_methods:
            previous_name = seen_methods[method_value]
            raise ImproperlyConfigured(
                f"{setting_name} and {previous_name} must be different for endpoint "
                f"{endpoint!r}. Got: {method_value!r}"
            )

        seen_methods[method_value] = setting_name


def register_endpoint_method_registry(
    *, endpoint: str, registry: list[dict[str, str]]
) -> dict[str, str]:
    normalized_endpoint = require_non_empty("endpoint", endpoint)
    validate_endpoint_method_registry(endpoint=normalized_endpoint, registry=registry)

    normalized_registry: dict[str, str] = {}
    for item in registry:
        setting_name = item["name"].strip()
        method_value = item["method"].strip().lower()
        normalized_registry[setting_name] = method_value

    REGISTERED_ENDPOINT_METHODS[normalized_endpoint] = normalized_registry
    return normalized_registry


def load_secret_key_for_settings(
    *,
    secret_key_file: str,
    base_dir: Path,
    bootstrap_command: str = "generatesecretkeyfile",
) -> str:
    normalized_secret_key_file = require_non_empty("SECRET_KEY_FILE", secret_key_file)
    secret_key_file_path = Path(normalized_secret_key_file)
    if not secret_key_file_path.is_absolute():
        secret_key_file_path = base_dir / secret_key_file_path
    secret_key_file_path = secret_key_file_path.resolve()

    if secret_key_file_path.exists():
        secret_key = secret_key_file_path.read_text(encoding="utf-8").strip()
        if not secret_key:
            raise ImproperlyConfigured(
                f"SECRET_KEY_FILE is empty: {secret_key_file_path!s}"
            )
        return secret_key

    if len(sys_argv) > 1 and sys_argv[1].strip().lower() == bootstrap_command.lower():
        # Allow bootstrapping command to run before SECRET_KEY_FILE is created.
        return "temporary-secret-key-for-generatesecretkeyfile-only"

    raise ImproperlyConfigured(
        f"SECRET_KEY_FILE does not exist: {secret_key_file_path!s}"
    )


def validate_source_directory(
    name: str,
    value: str,
    *,
    root_dir: Path,
    required_file: str = "Dockerfile",
) -> str:
    source = require_non_empty(name, value)
    source_path = Path(source)
    if not source_path.is_absolute():
        source_path = root_dir / source_path
    source_path = source_path.resolve()

    if not source_path.exists() or not source_path.is_dir():
        raise ImproperlyConfigured(
            f"{name} must point to an existing directory. Got: {source_path!s}"
        )

    if required_file and not (source_path / required_file).exists():
        raise ImproperlyConfigured(
            f"{name} must contain {required_file}. Got: {source_path!s}"
        )

    return str(source_path)
