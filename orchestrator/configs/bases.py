from pathlib import Path

from environ import Env

# Build paths inside the project like this: BASE_DIR / 'subdir'.
BASE_DIR = Path(__file__).resolve().parent.parent

env = Env(
    SECRET_KEY_FILE=(str, "secret/key.txt"),
    ALLOWED_HOSTS=(list[str], ["*"]),
    MANAGER_ADDRESS=(str, "manager"),
    DB_HOST=(str, "localhost"),
    DB_PORT=(str, "3306"),
    DB_USER=(str, "root"),
    DB_PASS=(str, ""),
    DB_NAME=(str, "defly"),
    LANGUAGE_CODE=(str, "vi-vn"),
    TIME_ZONE=(str, "Asia/Ho_Chi_Minh"),
    USE_I18N=(bool, True),
    USE_TZ=(bool, False),
)

Env.read_env(BASE_DIR / ".env")

# Quick-start development settings - unsuitable for production
# See https://docs.djangoproject.com/en/6.0/howto/deployment/checklist/

SECRET_KEY_FILE = env.str("SECRET_KEY_FILE")

with open(SECRET_KEY_FILE) as secret_key_file:
    SECRET_KEY = secret_key_file.read().strip()

ALLOWED_HOSTS = env.list("ALLOWED_HOSTS")


# Application definition

INSTALLED_APPS = [
    "app.bases",
]

MIDDLEWARE = [
    "django.middleware.security.SecurityMiddleware",
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
