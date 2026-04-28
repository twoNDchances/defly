from configs.bases import *  # noqa: F403

DEBUG = True

SERVER_DOCKER_BASE_URL = require_non_empty(  # noqa: F405
    "SERVER_DOCKER_BASE_URL",
    env.str("SERVER_DOCKER_BASE_URL"),  # noqa: F405
)
