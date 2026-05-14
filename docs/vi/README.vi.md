# Defly

Defly là hệ thống nhiều dịch vụ dùng để quản lý chính sách bảo mật, triển khai
Defender runtime và áp dụng lớp bảo vệ ứng dụng web ở biên.

## Kiến trúc

- `manager`: ứng dụng Laravel/Filament để quản trị người dùng, quyền, target,
  engine, rule, principle, decision, defender, report và timeline.
- `orchestrator`: dịch vụ Django ASGI được Manager gọi để deploy, theo dõi log
  và hủy container Defender thông qua Docker.
- `defender`: runtime viết bằng Go, gồm control API của Defender và WAF reverse
  proxy.
- `mariadb`: cơ sở dữ liệu dùng chung cho Manager, Orchestrator, worker và các
  Defender được deploy.

## Cài đặt bằng Docker

Docker Compose là cách khuyến nghị để chạy toàn bộ hệ thống.

### Yêu cầu

- Docker Engine hoặc Docker Desktop có Compose V2.
- Port `80` và `443` đang rảnh, hoặc đổi `MANAGER_HTTP_PORT` và
  `MANAGER_HTTPS_PORT`.

### 1. Tạo file môi trường ở repository root

Từ thư mục gốc của repository:

```powershell
Copy-Item .env.example .env
```

Kiểm tra lại `.env` trước khi chạy. Tối thiểu nên đặt các giá trị ổn định cho:

```text
MARIADB_ROOT_PASSWORD
DB_PASSWORD
ORCHESTRATOR_PASSWORD
APP_URL
USER_EMAIL
```

Nếu `USER_PASSWORD=random`, Manager sẽ tạo mật khẩu đăng nhập đầu tiên trong
file `/var/www/html/credentials.txt` bên trong container Manager.

### 2. Build image Defender

Orchestrator deploy Defender từ image local được cấu hình bởi
`SERVER_DEFENDER_IMAGE`. Cần build riêng vì service Defender trong Compose chỉ
dùng như profile build image.

```powershell
docker compose build defender
```

### 3. Khởi động stack

```powershell
docker compose up -d --build
```

Kiểm tra trạng thái service:

```powershell
docker compose ps
```

Theo dõi log khi service vẫn đang khởi động:

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

### 4. Mở Manager

URL mặc định:

```text
https://localhost/defly-manager
```

Manager tự tạo certificate self-signed ở lần chạy đầu. Trình duyệt có thể yêu
cầu xác nhận certificate này.

Nếu mật khẩu user đầu tiên được random, đọc bằng lệnh:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

### 5. Một số lệnh Docker hữu ích

Chạy Artisan trong Manager:

```powershell
docker compose exec manager php artisan migrate --force
docker compose exec manager php artisan db:seed --force
docker compose exec manager php artisan optimize
```

Restart stack:

```powershell
docker compose restart
```

Dừng container nhưng giữ dữ liệu:

```powershell
docker compose down
```

Dừng container và xóa named volume:

```powershell
docker compose down -v
```

Chỉ dùng `down -v` khi bạn thật sự muốn xóa database và runtime storage.

### 6. Ghi chú khi deploy Defender

Khi deploy Defender từ Manager, Orchestrator sẽ:

- dùng image được cấu hình bởi `SERVER_DEFENDER_IMAGE`
- tạo hoặc tái sử dụng volume error/log riêng cho từng Defender
- dùng volume chung `SERVER_DEFENDER_TLS_VOLUME` cho file TLS của Defender
- gắn container Defender vào cùng Compose network với Orchestrator
- expose proxy port được cấu hình trong bản ghi Defender

Manager và Orchestrator phải dùng cùng database và cùng bộ credential
Orchestrator. File `docker-compose.yml` hiện tại đã nối các giá trị này từ
file `.env` ở root.

## Cài đặt thủ công

Cài thủ công phù hợp cho môi trường phát triển. Bạn vẫn cần Docker nếu muốn
Orchestrator deploy container Defender.

### Yêu cầu

- MariaDB hoặc MySQL.
- PHP `8.3+`, Composer `2`, và các PHP extension Manager cần:
  `bcmath`, `curl`, `exif`, `gd`, `intl`, `mbstring`, `pcntl`, `pdo_mysql`,
  `zip`.
- Node.js và npm để build asset cho Manager.
- Python `3.14+`, `uv`, và MySQL client/build libraries cho Orchestrator.
- Go `1.26.1+` để phát triển Defender.
- Docker Engine/Desktop nếu dùng chức năng deploy qua Orchestrator.

### 1. Chuẩn bị database

Tạo database và user cho Defly:

```sql
CREATE DATABASE defly_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'defly'@'%' IDENTIFIED BY 'change_me_app';
GRANT ALL PRIVILEGES ON defly_manager.* TO 'defly'@'%';
FLUSH PRIVILEGES;
```

Dùng cùng thông tin database này cho Manager, Orchestrator và Defender.

### 2. Cài Manager

Từ thư mục `manager`:

```powershell
cd manager
Copy-Item .env.example .env
composer install
npm install
```

Sửa `manager/.env`:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=defly_manager
DB_USERNAME=defly
DB_PASSWORD=change_me_app

ORCHESTRATOR_BASE_URL=http://127.0.0.1:8000
ORCHESTRATOR_TLS_SKIP_VERIFY=true

USER_EMAIL=root@defly.local
USER_PASSWORD=random
```

Khởi tạo ứng dụng Laravel:

```powershell
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
npm run build
```

Nếu `USER_PASSWORD=random`, đọc mật khẩu được tạo:

```powershell
Get-Content credentials.txt
```

Chạy Manager:

```powershell
php artisan serve --host=127.0.0.1 --port=8080
```

Chạy queue worker ở terminal khác:

```powershell
php artisan queue:work --tries=3
```

URL Manager trong cách chạy thủ công này:

```text
http://127.0.0.1:8080/defly-manager
```

### 3. Cài Orchestrator

Orchestrator đọc các bảng database do Manager tạo. Hãy chạy migration của
Manager trước khi khởi động Orchestrator.

Từ thư mục `orchestrator`:

```powershell
cd orchestrator
Copy-Item .env.example .env
uv sync
```

Sửa `orchestrator/.env`:

```text
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=defly
DB_PASS=change_me_app
DB_NAME=defly_manager

SERVER_MANAGER=127.0.0.1,localhost
SERVER_USERNAME=defly-orchestrator
SERVER_PASSWORD=P@55w0rd
SERVER_DEFENDER_IMAGE=defly-defender:latest
SERVER_DEFENDER_TLS_VOLUME=defender_tls
SERVER_DOCKER_BASE_URL=tcp://localhost:2375
```

`SERVER_DOCKER_BASE_URL` phải trỏ tới Docker daemon mà process Orchestrator có
thể truy cập. Với Docker Desktop, chỉ bật TCP Docker API trên máy phát triển
local đáng tin cậy. Trên Linux có thể dùng `unix:///var/run/docker.sock`.

Tạo file Django secret key local:

```powershell
uv run python manage.py generatesecretkeyfile
```

Chạy Orchestrator bằng HTTP cho phát triển local:

```powershell
$env:DJANGO_SETTINGS_MODULE = "configs.development"
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

Nếu muốn chạy Orchestrator bằng HTTPS, tạo TLS file và truyền cert/key cho
Uvicorn:

```powershell
uv run python manage.py generatetlsfile
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000 --ssl-certfile ".\storage\tls\orchestrator.crt" --ssl-keyfile ".\storage\tls\orchestrator.key"
```

Nếu dùng HTTPS, cập nhật lại `ORCHESTRATOR_BASE_URL`,
`ORCHESTRATOR_TLS_SKIP_VERIFY` và `ORCHESTRATOR_TLS_CERT_FILE` bên Manager.

### 4. Link TLS file khi cài thủ công

Docker Compose đã mount sẵn các TLS volume cho Manager. Khi cài thủ công ở
local, tạo link để Manager đọc được certificate do Orchestrator hoặc Defender
thủ công sinh ra.

Repository đang giữ các thư mục TLS placeholder bằng file `.gitignore`. Trên
Windows, chạy từ repository root và tạo junction con tên `shared` bên trong các
thư mục đó:

```powershell
New-Item -ItemType Directory -Force manager\storage\tls
New-Item -ItemType Directory -Force manager\storage\tls\orchestrator
New-Item -ItemType Directory -Force manager\storage\tls\defenders
New-Item -ItemType Directory -Force orchestrator\storage\tls
New-Item -ItemType Directory -Force defender\storage\tls

New-Item -ItemType Junction -Path manager\storage\tls\orchestrator\shared -Target (Resolve-Path orchestrator\storage\tls)
New-Item -ItemType Junction -Path manager\storage\tls\defenders\shared -Target (Resolve-Path defender\storage\tls)
```

Dùng cấu hình TLS này trong Manager khi đã tạo các junction trên:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY=false
ORCHESTRATOR_TLS_CERT_FILE=storage/tls/orchestrator/shared/orchestrator.crt
DEFENDER_SERVER_TLS_SKIP_VERIFY=false
DEFENDER_SERVER_TLS_DIRECTORY=storage/tls/defenders/shared
```

Nếu `manager/storage/tls/orchestrator/shared` hoặc
`manager/storage/tls/defenders/shared` đã tồn tại, hãy move hoặc xóa link đó
trước khi tạo lại. Junction link thư mục, không link từng file riêng lẻ.

Nếu muốn giữ đường dẫn TLS mặc định của Manager, dùng file symlink cho các file
Orchestrator:

Trên Windows, file symlink có thể cần bật Developer Mode hoặc chạy terminal
với quyền administrator.

```powershell
New-Item -ItemType Directory -Force manager\storage\tls\orchestrator
New-Item -ItemType SymbolicLink -Path manager\storage\tls\orchestrator\orchestrator.crt -Target (Resolve-Path orchestrator\storage\tls\orchestrator.crt)
New-Item -ItemType SymbolicLink -Path manager\storage\tls\orchestrator\orchestrator.key -Target (Resolve-Path orchestrator\storage\tls\orchestrator.key)
```

Manager chỉ cần file `.crt` để verify Orchestrator. Link thêm `.key` là tùy
chọn, chủ yếu để giữ cặp TLS local đi cùng nhau.

Với Defender chạy thủ công, link các file certificate theo đúng tên Defender:

```powershell
New-Item -ItemType Directory -Force manager\storage\tls\defenders
New-Item -ItemType SymbolicLink -Path manager\storage\tls\defenders\local-defender.crt -Target (Resolve-Path defender\storage\tls\local-defender.crt)
New-Item -ItemType SymbolicLink -Path manager\storage\tls\defenders\local-defender.key -Target (Resolve-Path defender\storage\tls\local-defender.key)
```

Thay `local-defender` bằng đúng giá trị `DEFENDER_NAME`. Manager verify
Defender bằng `{DEFENDER_SERVER_TLS_DIRECTORY}/{DEFENDER_NAME}.crt`; link
`.key` là tùy chọn đối với Manager.

Trên Linux hoặc macOS, dùng directory symlink:

```sh
mkdir -p manager/storage/tls/orchestrator manager/storage/tls/defenders orchestrator/storage/tls defender/storage/tls
ln -sfn "$(pwd)/orchestrator/storage/tls" manager/storage/tls/orchestrator/shared
ln -sfn "$(pwd)/defender/storage/tls" manager/storage/tls/defenders/shared
```

Khi Defender được Orchestrator deploy vào Docker, TLS file của Defender nằm
trong Docker named volume `defender_tls`. Manager chạy trực tiếp trên host sẽ
không đọc được volume đó; khi phát triển local, hãy chạy full stack bằng Docker
Compose hoặc đặt `DEFENDER_SERVER_TLS_SKIP_VERIFY=true`.

### 5. Build Defender để Orchestrator deploy

Từ repository root:

```powershell
docker build -t defly-defender:latest ./defender
docker volume create defender_tls
```

Volume chung `defender_tls` phải tồn tại trước khi Orchestrator deploy
Defender. Các volume error/log riêng của từng Defender sẽ được Orchestrator tạo
khi cần.

### 6. Chạy Defender thủ công

Thông thường Defender được tạo từ Manager thông qua Orchestrator. Nếu cần chạy
riêng để phát triển Defender, chạy trực tiếp từ thư mục `defender` sau khi
database đã được Manager migrate và seed.

```powershell
cd defender
go mod download

$env:DATABASE_HOST = "127.0.0.1"
$env:DATABASE_PORT = "3306"
$env:DATABASE_NAME = "defly_manager"
$env:DATABASE_USER = "defly"
$env:DATABASE_PASS = "change_me_app"
$env:DEFENDER_NAME = "local-defender"
$env:PROXY_BACKEND_URL = "http://127.0.0.1:8080"

go run ./cmd/defender
```

Port mặc định của Defender:

- `9947`: Defender control API
- `9948`: Defender proxy

### 7. Kiểm tra khi phát triển

Manager:

```powershell
cd manager
php artisan test
```

Orchestrator:

```powershell
cd orchestrator
uv run python manage.py check
uv run python -m ruff check .
```

Defender:

```powershell
cd defender
go test ./...
```
