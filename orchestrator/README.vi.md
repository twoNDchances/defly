# Defly Orchestrator

Defly Orchestrator là dịch vụ Django ASGI được Defly Manager dùng để deploy,
follow log, và cancel các container Defender thông qua Docker.

## Yêu cầu

- Python 3.14+
- `uv`
- MySQL client/build support cho `mysqlclient`
- Quyền truy cập Docker API từ process Orchestrator

## Cài đặt

Cài dependency từ thư mục `orchestrator`:

```powershell
cd orchestrator
uv sync
```

Tạo file môi trường local:

```powershell
Copy-Item .env.example .env
```

Tạo file Django secret key local:

```powershell
uv run python manage.py generatesecretkeyfile
```

TLS là tùy chọn trong môi trường local. Chỉ tạo certificate và key TLS khi bạn
muốn chạy Orchestrator bằng HTTPS:

```powershell
uv run python manage.py generatetlsfile
```

Những file được tạo sẽ nằm ở:

```text
storage/secret/key.txt
storage/tls/orchestrator.crt
storage/tls/orchestrator.key
```

Đây là các file runtime storage và không nên commit.

## Môi trường

Những cấu hình chính trong `.env`:

```text
SECRET_KEY_FILE="storage/secret/key.txt"
ALLOWED_HOSTS="127.0.0.1,localhost,manager,orchestrator"

DB_HOST="localhost"
DB_PORT="3306"
DB_USER="root"
DB_PASS=""
DB_NAME="defly_manager"

SERVER_MANAGER="manager"
SERVER_USERNAME="defly-orchestrator"
SERVER_PASSWORD="P@55w0rd"

SERVER_PATH_PREFIX="api/v1"
SERVER_PATH_DEPLOYMENT="deployments"
SERVER_METHOD_DEPLOY="post"
SERVER_METHOD_FOLLOW="get"
SERVER_METHOD_CANCEL="delete"
SERVER_SOURCE_DEFENDER="./defender"
SERVER_DEFENDER_TLS_VOLUME="defender_tls"
SERVER_DOCKER_BASE_URL="tcp://localhost:2375"
```

`SERVER_MANAGER` được middleware dùng để chỉ cho phép request từ Manager. Khi
phát triển local, đặt giá trị này thành `localhost` nếu Manager không chạy bằng
Docker host tên `manager`.

`SERVER_SOURCE_DEFENDER` được resolve từ repository root. Giá trị mặc định
`./defender` trỏ tới project Defender nằm cùng cấp với Orchestrator.

`SERVER_DEFENDER_TLS_VOLUME` là Docker volume key dùng cho file TLS của
Defender. Khi Orchestrator chạy trong Docker Compose, key này sẽ được resolve
theo Compose project hiện tại, nên mặc định `defender_tls` sẽ thành volume
thật như `defly_defender_tls`.

## Chạy

Từ thư mục `orchestrator`, chạy không TLS bằng HTTP:

```powershell
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

Nếu chạy từ repository root, dùng `--project` và `--app-dir`:

```powershell
uv --project .\orchestrator run uvicorn configs.asgi:application --app-dir .\orchestrator --reload --host 0.0.0.0 --port 8000
```

Để chạy có TLS bằng HTTPS, tạo TLS files trước rồi truyền certificate và key
cho Uvicorn:

```powershell
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000 --ssl-certfile ".\storage\tls\orchestrator.crt" --ssl-keyfile ".\storage\tls\orchestrator.key"
```

Từ repository root:

```powershell
uv --project .\orchestrator run uvicorn configs.asgi:application --app-dir .\orchestrator --reload --host 0.0.0.0 --port 8000 --ssl-certfile ".\orchestrator\storage\tls\orchestrator.crt" --ssl-keyfile ".\orchestrator\storage\tls\orchestrator.key"
```

## Docker

Build image Orchestrator từ thư mục `orchestrator`:

```powershell
docker build -t defly-orchestrator .
```

Khi chạy bằng Docker Compose, mount persistent storage và Docker socket để
Orchestrator có thể tạo container Defender:

```yaml
services:
  orchestrator:
    image: defly-orchestrator
    volumes:
      - orchestrator_storage:/app/storage
      - /var/run/docker.sock:/var/run/docker.sock
      - ../defender:/defender:ro
    environment:
      SERVER_SOURCE_DEFENDER: /defender
      SERVER_DEFENDER_TLS_VOLUME: defender_tls

volumes:
  orchestrator_storage:
  defender_tls:
```

Docker image mặc định khởi động Orchestrator bằng TLS. Ở lần chạy đầu tiên,
entrypoint sẽ tạo `storage/secret/key.txt` và cặp TLS local trong `storage/tls`
nếu các file đó chưa tồn tại.

## API

Base path:

```text
/{SERVER_PATH_PREFIX}/{SERVER_PATH_DEPLOYMENT}/{defender_id}
```

Route mặc định:

```text
/api/v1/deployments/{defender_id}
```

Method mặc định:

```text
POST   deploy defender
GET    follow defender logs
DELETE cancel defender
```

Tất cả request cần Basic Authentication bằng `SERVER_USERNAME` và
`SERVER_PASSWORD`.

Ví dụ HTTP:

```powershell
curl.exe -u "defly-orchestrator:P@55w0rd" -X GET "http://localhost:8000/api/v1/deployments/<defender-id>"
```

Ví dụ HTTPS với certificate self-signed local:

```powershell
curl.exe -k -u "defly-orchestrator:P@55w0rd" -X GET "https://localhost:8000/api/v1/deployments/<defender-id>"
```

## Storage khi deploy Defender

Khi deploy một Defender, Orchestrator sẽ:

- build Docker image của Defender từ `SERVER_SOURCE_DEFENDER`
- inject `DEFENDER_NAME` từ tên Defender bên Manager
- join Defender vào cùng Docker Compose network với Orchestrator khi
  Orchestrator đang chạy trong Docker Compose
- mount volume errors của Defender vào `/app/storage/errors`
- mount volume logs của Defender vào `/app/storage/logs`
- mount volume TLS dùng chung của Defender vào `/app/storage/tls`

Volume errors và logs phải tồn tại trước khi deploy. Tên volume dùng container
name đã được normalize của Defender cộng với `_errors` hoặc `_logs`. Với
Defender tên `edge-01`, khai báo Compose volumes là `edge-01_errors` và
`edge-01_logs`.

Volume `defender_tls` dùng chung cũng được tạo bởi Docker Compose và không có
tên Defender. Manager nên mount cùng volume đó vào thư mục storage TLS của
Defender, ví dụ:

```yaml
services:
  manager:
    volumes:
      - defender_tls:/var/www/html/storage/tls/defenders

  orchestrator:
    environment:
      SERVER_DEFENDER_TLS_VOLUME: defender_tls

volumes:
  edge-01_errors:
  edge-01_logs:
  defender_tls:
```

File TLS của Defender sẽ được ghi trong volume TLS dùng chung dưới dạng
`{DEFENDER_NAME}.crt` và `{DEFENDER_NAME}.key`.

Khi phát triển local bên ngoài Docker Compose, Orchestrator dùng raw volume name
không có Compose project prefix. Tạo thủ công các volume đó trước khi deploy,
ví dụ:

```powershell
docker volume create edge-01_errors
docker volume create edge-01_logs
docker volume create defender_tls
```

Ở local mode, Orchestrator không thể tự suy ra Compose network từ container của
chính nó, nên Docker sẽ dùng network mặc định trừ khi bạn chạy cả stack bằng
Docker Compose.

## Kiểm tra

```powershell
uv run python manage.py check
uv run python -m ruff check .
```
