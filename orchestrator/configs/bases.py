from pathlib import Path

from environ import Env

from configs.validators import (
    load_secret_key_for_settings,
    register_endpoint_method_registry,
    require_non_empty,
    validate_http_method,
    validate_path_value,
    validate_source_directory,
    validate_username,
)

# Build paths inside the project like this: BASE_DIR / 'subdir'.
BASE_DIR = Path(__file__).resolve().parent.parent

env = Env(
    SECRET_KEY_FILE=(str, "secret/key.txt"),
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
    SERVER_PATH_PREFIX=(str, "api/v1"),
    SERVER_PATH_DEPLOYMENT=(str, "deployments"),
    SERVER_METHOD_DEPLOY=(str, "post"),
    SERVER_METHOD_FOLLOW=(str, "get"),
    SERVER_METHOD_CANCEL=(str, "delete"),
    SERVER_SOURCE_DEFENDER=(str, "./defender"),
)

Env.read_env(BASE_DIR / ".env")

# Quick-start development settings - unsuitable for production
# See https://docs.djangoproject.com/en/6.0/howto/deployment/checklist/

SECRET_KEY = load_secret_key_for_settings(
    secret_key_file=env.str("SECRET_KEY_FILE"), base_dir=BASE_DIR
)

ALLOWED_HOSTS = env.list("ALLOWED_HOSTS")


# Application definition

INSTALLED_APPS = [
    "app.bases",
    "app.deployments",
]

MIDDLEWARE = [
    "django.middleware.security.SecurityMiddleware",
    "app.bases.middlewares.ServerManagerOnlyMiddleware",
    "app.bases.middlewares.ServerBasicAuthMiddleware",
    "django.middleware.common.CommonMiddleware",
    "django.middleware.csrf.CsrfViewMiddleware",
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
SERVER_PATH_PREFIX = validate_path_value(
    "SERVER_PATH_PREFIX", env.str("SERVER_PATH_PREFIX")
)
SERVER_PATH_DEPLOYMENT = validate_path_value(
    "SERVER_PATH_DEPLOYMENT", env.str("SERVER_PATH_DEPLOYMENT")
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
SERVER_SOURCE_DEFENDER = validate_source_directory(
    "SERVER_SOURCE_DEFENDER",
    env.str("SERVER_SOURCE_DEFENDER"),
    root_dir=BASE_DIR.parent,
)
