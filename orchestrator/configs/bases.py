from pathlib import Path

from environ import Env

from configs.validators import (
    load_secret_key_for_settings,
    register_endpoint_method_registry,
    require_non_empty,
    validate_http_method,
    validate_path_value,
    validate_username,
)

# Build paths inside the project like this: BASE_DIR / 'subdir'.
BASE_DIR = Path(__file__).resolve().parent.parent

env = Env(
    SECRET_KEY_FILE=(str, "storage/secret/key.txt"),
    ALLOWED_HOSTS=(list[str], ["*"]),
    DB_HOST=(str, "localhost"),
    DB_PORT=(str, "3306"),
    DB_USER=(str, "root"),
    DB_PASS=(str, ""),
    DB_NAME=(str, "defly"),
    LANGUAGE_CODE=(str, "vi-vn"),
    TIME_ZONE=(str, "Asia/Ho_Chi_Minh"),
    USE_I18N=(bool, True),
    USE_TZ=(bool, False),
    SERVER_MANAGER=(str, "manager"),
    SERVER_USERNAME=(str, "defly-orchestrator"),
    SERVER_PASSWORD=(str, "P@55w0rd"),
    SERVER_EMAIL_HEADER_KEY=(str, "X-Executor"),
    SERVER_PATH_PREFIX=(str, "api/v1"),
    SERVER_PATH_DEPLOYMENT=(str, "deployments"),
    SERVER_PATH_ASSISTANT=(str, "assistant"),
    SERVER_METHOD_ASSISTANT=(str, "get"),
    SERVER_METHOD_DEPLOY=(str, "post"),
    SERVER_METHOD_FOLLOW=(str, "get"),
    SERVER_METHOD_CANCEL=(str, "delete"),
    SERVER_DEFENDER_IMAGE=(str, "defly-defender:latest"),
    SERVER_DEFENDER_TLS_VOLUME=(str, "defender_tls"),
    SERVER_DOCKER_BASE_URL=(str, "tcp://localhost:2375"),
    AI_BASE_URL=(str, "https://api.openai.com/v1"),
    AI_MODEL=(str, "gpt-4.1-mini"),
    AI_TIMEOUT=(float, 90.0),
    AI_MAX_MESSAGES=(int, 40),
    AI_MAX_MESSAGE_CHARACTERS=(int, 4000),
    AI_API_KEY=(str, ""),
)

Env.read_env(BASE_DIR / ".env")

# Quick-start development settings - unsuitable for production
# See https://docs.djangoproject.com/en/6.0/howto/deployment/checklist/

SECRET_KEY_FILE = env.str("SECRET_KEY_FILE")

SECRET_KEY = load_secret_key_for_settings(
    secret_key_file=SECRET_KEY_FILE, base_dir=BASE_DIR
)

ALLOWED_HOSTS = env.list("ALLOWED_HOSTS")


# Application definition

INSTALLED_APPS = [
    "app.bases",
    "app.assistant",
    "app.deployments",
]

MIDDLEWARE = [
    "django.middleware.security.SecurityMiddleware",
    "app.bases.middlewares.ServerManagerOnlyMiddleware",
    "app.bases.middlewares.ServerBasicAuthMiddleware",
    "app.deployments.middlewares.ServerPermissionMiddleware",
    "app.assistant.middlewares.ServerPermissionMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.clickjacking.XFrameOptionsMiddleware",
]

ROOT_URLCONF = "configs.urls"

TEMPLATES = [
    {
        "BACKEND": "django.template.backends.django.DjangoTemplates",
        "DIRS": [],
        "APP_DIRS": True,
        "OPTIONS": {
            "context_processors": [
                "django.template.context_processors.request",
            ],
        },
    },
]


# Database
# https://docs.djangoproject.com/en/6.0/ref/settings/#databases

DATABASES = {
    "default": {
        "ENGINE": "django.db.backends.mysql",
        "HOST": env.str("DB_HOST"),
        "NAME": env.str("DB_NAME"),
        "USER": env.str("DB_USER"),
        "PASSWORD": env.str("DB_PASS"),
        "PORT": env.str("DB_PORT"),
    }
}


# Internationalization
# https://docs.djangoproject.com/en/6.0/topics/i18n/

LANGUAGE_CODE = env.str("LANGUAGE_CODE")

TIME_ZONE = env.str("TIME_ZONE")

USE_I18N = env.bool("USE_I18N")

USE_TZ = env.bool("USE_TZ")

# Server customizations

SERVER_MANAGER = require_non_empty("SERVER_MANAGER", env.str("SERVER_MANAGER"))
SERVER_USERNAME = validate_username("SERVER_USERNAME", env.str("SERVER_USERNAME"))
SERVER_PASSWORD = require_non_empty("SERVER_PASSWORD", env.str("SERVER_PASSWORD"))
SERVER_EMAIL_HEADER_KEY = require_non_empty(
    "SERVER_EMAIL_HEADER_KEY",
    env.str("SERVER_EMAIL_HEADER_KEY"),
)
SERVER_PATH_PREFIX = validate_path_value(
    "SERVER_PATH_PREFIX", env.str("SERVER_PATH_PREFIX")
)
SERVER_PATH_DEPLOYMENT = validate_path_value(
    "SERVER_PATH_DEPLOYMENT", env.str("SERVER_PATH_DEPLOYMENT")
)
SERVER_PATH_ASSISTANT = validate_path_value(
    "SERVER_PATH_ASSISTANT", env.str("SERVER_PATH_ASSISTANT")
)
SERVER_METHOD_ASSISTANT = validate_http_method(
    "SERVER_METHOD_ASSISTANT", env.str("SERVER_METHOD_ASSISTANT")
)
SERVER_METHOD_DEPLOY = validate_http_method(
    "SERVER_METHOD_DEPLOY", env.str("SERVER_METHOD_DEPLOY")
)
SERVER_METHOD_FOLLOW = validate_http_method(
    "SERVER_METHOD_FOLLOW", env.str("SERVER_METHOD_FOLLOW")
)
SERVER_METHOD_CANCEL = validate_http_method(
    "SERVER_METHOD_CANCEL", env.str("SERVER_METHOD_CANCEL")
)
SERVER_DEPLOYMENT_METHODS = register_endpoint_method_registry(
    endpoint=SERVER_PATH_DEPLOYMENT,
    registry=[
        {"name": "SERVER_METHOD_DEPLOY", "method": SERVER_METHOD_DEPLOY},
        {"name": "SERVER_METHOD_FOLLOW", "method": SERVER_METHOD_FOLLOW},
        {"name": "SERVER_METHOD_CANCEL", "method": SERVER_METHOD_CANCEL},
    ],
)
SERVER_DEFENDER_IMAGE = require_non_empty(
    "SERVER_DEFENDER_IMAGE",
    env.str("SERVER_DEFENDER_IMAGE"),
)
SERVER_DEFENDER_TLS_VOLUME = require_non_empty(
    "SERVER_DEFENDER_TLS_VOLUME",
    env.str("SERVER_DEFENDER_TLS_VOLUME"),
)

# AI assistant

AI_BASE_URL = require_non_empty("AI_BASE_URL", env.str("AI_BASE_URL"))
AI_MODEL = require_non_empty("AI_MODEL", env.str("AI_MODEL"))
AI_TIMEOUT = env.float("AI_TIMEOUT")
AI_MAX_MESSAGES = max(0, env.int("AI_MAX_MESSAGES"))
AI_MAX_MESSAGE_CHARACTERS = max(0, env.int("AI_MAX_MESSAGE_CHARACTERS"))
AI_API_KEY = env.str("AI_API_KEY").strip()
