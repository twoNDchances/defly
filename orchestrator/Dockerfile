FROM python:3.14-slim AS builder

COPY --from=ghcr.io/astral-sh/uv:latest /uv /uvx /usr/local/bin/

ENV UV_COMPILE_BYTECODE=1
ENV UV_LINK_MODE=copy

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        build-essential \
        default-libmysqlclient-dev \
        pkg-config \
    && rm -rf /var/lib/apt/lists/*

COPY pyproject.toml uv.lock ./
RUN uv sync --frozen --no-dev --no-install-project

COPY . .
RUN uv sync --frozen --no-dev

FROM python:3.14-slim

ENV DJANGO_SETTINGS_MODULE=configs.production
ENV ORCHESTRATOR_TLS_CERT_FILE=storage/tls/orchestrator.crt
ENV ORCHESTRATOR_TLS_KEY_FILE=storage/tls/orchestrator.key
ENV PATH="/app/.venv/bin:${PATH}"
ENV PYTHONUNBUFFERED=1

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends libmariadb3 \
    && rm -rf /var/lib/apt/lists/* \
    && addgroup --system orchestrator \
    && adduser --system --ingroup orchestrator --home /app orchestrator

COPY --from=builder --chown=orchestrator:orchestrator /app /app

RUN mkdir -p /app/storage/secret /app/storage/tls \
    && chmod +x /app/entrypoint.sh \
    && chown -R orchestrator:orchestrator /app

EXPOSE 8000

USER orchestrator

ENTRYPOINT ["/app/entrypoint.sh"]
CMD ["sh", "-c", "exec uvicorn configs.asgi:application --host 0.0.0.0 --port 8000 --ssl-certfile \"$ORCHESTRATOR_TLS_CERT_FILE\" --ssl-keyfile \"$ORCHESTRATOR_TLS_KEY_FILE\""]
