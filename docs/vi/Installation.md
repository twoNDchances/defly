# Cài đặt

Có hai cách cài đặt Defly:

- Cài bằng Docker Compose để chạy toàn bộ hệ thống.
- Cài thủ công để phát triển từng dịch vụ.

## Cài bằng Docker Compose

Docker Compose là cách khuyến nghị để chạy toàn bộ hệ thống.

### 1. Tạo tệp môi trường ở thư mục gốc

Từ thư mục gốc của kho mã nguồn:

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
tệp `/var/www/html/credentials.txt` bên trong container Manager.

### 2. Dựng image Defender

Orchestrator triển khai Defender từ image cục bộ được cấu hình bởi
`SERVER_DEFENDER_IMAGE`. Cần dựng riêng image này vì dịch vụ Defender trong
Compose chỉ dùng như hồ sơ dựng image.

```powershell
docker compose build defender
```

### 3. Khởi động hệ thống

```powershell
docker compose up -d --build
```

Kiểm tra trạng thái dịch vụ:

```powershell
docker compose ps
```

Theo dõi nhật ký khi dịch vụ vẫn đang khởi động:

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

### 4. Mở Manager

Địa chỉ mặc định:

```text
https://localhost/defly-manager
```

Manager tự tạo chứng chỉ tự ký ở lần chạy đầu. Trình duyệt có thể yêu cầu xác
nhận chứng chỉ này.

Nếu mật khẩu người dùng đầu tiên được tạo ngẫu nhiên, đọc bằng lệnh:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

### 5. Một số lệnh Docker hữu ích

Chạy lệnh Artisan trong Manager:

```powershell
docker compose exec manager php artisan migrate --force
docker compose exec manager php artisan db:seed --force
docker compose exec manager php artisan optimize
```

Khởi động lại toàn bộ hệ thống:

```powershell
docker compose restart
```

Dừng container nhưng giữ dữ liệu:

```powershell
docker compose down
```

Dừng container và xóa volume có tên:

```powershell
docker compose down -v
```

Chỉ dùng `down -v` khi bạn thật sự muốn xóa cơ sở dữ liệu và dữ liệu chạy của
hệ thống.

### 6. Ghi chú khi triển khai Defender

Khi triển khai Defender từ Manager, Orchestrator sẽ:

- dùng image được cấu hình bởi `SERVER_DEFENDER_IMAGE`
- tạo hoặc tái sử dụng volume lỗi và nhật ký riêng cho từng Defender
- dùng volume chung `SERVER_DEFENDER_TLS_VOLUME` cho tệp TLS của Defender
- gắn container Defender vào cùng mạng Compose với Orchestrator
- mở cổng proxy được cấu hình trong bản ghi Defender

Manager và Orchestrator phải dùng cùng cơ sở dữ liệu và cùng bộ thông tin xác
thực Orchestrator. Tệp `docker-compose.yml` hiện tại đã nối các giá trị này từ
tệp `.env` ở thư mục gốc.

## Cài thủ công

Cài thủ công phù hợp cho môi trường phát triển. Bạn vẫn cần Docker nếu muốn
Orchestrator triển khai container Defender.

### Yêu cầu

- MariaDB hoặc MySQL.
- PHP `8.3+`, Composer `2`, và các phần mở rộng PHP mà Manager cần:
  `bcmath`, `curl`, `exif`, `gd`, `intl`, `mbstring`, `pcntl`, `pdo_mysql`,
  `zip`.
- Node.js và npm để dựng tài nguyên giao diện cho Manager.
- Python `3.14+`, `uv`, và thư viện MySQL cho Orchestrator.
- Go `1.26.1+` để phát triển Defender.
- Docker Engine hoặc Docker Desktop nếu dùng chức năng triển khai qua
  Orchestrator.

### 1. Chuẩn bị cơ sở dữ liệu

Tạo cơ sở dữ liệu và người dùng cho Defly:

```sql
CREATE DATABASE defly_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'defly'@'%' IDENTIFIED BY 'change_me_app';
GRANT ALL PRIVILEGES ON defly_manager.* TO 'defly'@'%';
FLUSH PRIVILEGES;
```

Dùng cùng thông tin cơ sở dữ liệu này cho Manager, Orchestrator và Defender.

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

Chạy tiến trình hàng đợi ở cửa sổ lệnh khác:

```powershell
php artisan queue:work --tries=3
```

Địa chỉ Manager trong cách chạy thủ công này:

```text
http://127.0.0.1:8080/defly-manager
```

### 3. Cài Orchestrator

Orchestrator đọc các bảng cơ sở dữ liệu do Manager tạo. Hãy chạy migration của
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

`SERVER_DOCKER_BASE_URL` phải trỏ tới Docker daemon mà tiến trình
Orchestrator có thể truy cập. Với Docker Desktop, chỉ bật TCP Docker API trên
máy phát triển cục bộ đáng tin cậy. Trên Linux có thể dùng
`unix:///var/run/docker.sock`.

Tạo tệp khóa bí mật Django cục bộ:

```powershell
uv run python manage.py generatesecretkeyfile
```

Chạy Orchestrator bằng HTTP cho phát triển cục bộ:

```powershell
$env:DJANGO_SETTINGS_MODULE = "configs.development"
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

Nếu muốn chạy Orchestrator bằng HTTPS, tạo tệp TLS và truyền chứng chỉ, khóa
cho Uvicorn:

```powershell
uv run python manage.py generatetlsfile
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000 --ssl-certfile ".\storage\tls\orchestrator.crt" --ssl-keyfile ".\storage\tls\orchestrator.key"
```

Nếu dùng HTTPS, cập nhật lại `ORCHESTRATOR_BASE_URL`,
`ORCHESTRATOR_TLS_SKIP_VERIFY` và `ORCHESTRATOR_TLS_CERT_FILE` bên Manager.

### 4. Liên kết tệp TLS khi cài thủ công

Docker Compose đã gắn sẵn các volume TLS cho Manager. Khi cài thủ công ở máy
cục bộ, tạo liên kết để Manager đọc được chứng chỉ do Orchestrator hoặc
Defender thủ công sinh ra.

Kho mã nguồn đang giữ các thư mục TLS rỗng bằng tệp `.gitignore`. Trên
Windows, chạy từ thư mục gốc của kho mã nguồn và tạo junction tên `shared` bên
trong các thư mục đó:

```powershell
New-Item -ItemType Directory -Force manager\storage\tls
New-Item -ItemType Directory -Force manager\storage\tls\orchestrator
New-Item -ItemType Directory -Force manager\storage\tls\defenders
New-Item -ItemType Directory -Force orchestrator\storage\tls
New-Item -ItemType Directory -Force defender\storage\tls

New-Item -ItemType Junction -Path manager\storage\tls\orchestrator\shared -Target (Resolve-Path orchestrator\storage\tls)
New-Item -ItemType Junction -Path manager\storage\tls\defenders\shared -Target (Resolve-Path defender\storage\tls)
```

Dùng cấu hình TLS này trong Manager sau khi đã tạo các junction:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY=false
ORCHESTRATOR_TLS_CERT_FILE=storage/tls/orchestrator/shared/orchestrator.crt
DEFENDER_SERVER_TLS_SKIP_VERIFY=false
DEFENDER_SERVER_TLS_DIRECTORY=storage/tls/defenders/shared
```

Nếu `manager/storage/tls/orchestrator/shared` hoặc
`manager/storage/tls/defenders/shared` đã tồn tại, hãy di chuyển hoặc xóa liên
kết đó trước khi tạo lại. Junction liên kết thư mục, không liên kết từng tệp
riêng lẻ.

Nếu muốn giữ đường dẫn TLS mặc định của Manager, dùng symlink cho các tệp của
Orchestrator:

Trên Windows, symlink tệp có thể cần bật Developer Mode hoặc chạy cửa sổ lệnh
với quyền quản trị viên.

```powershell
New-Item -ItemType Directory -Force manager\storage\tls\orchestrator
New-Item -ItemType SymbolicLink -Path manager\storage\tls\orchestrator\orchestrator.crt -Target (Resolve-Path orchestrator\storage\tls\orchestrator.crt)
New-Item -ItemType SymbolicLink -Path manager\storage\tls\orchestrator\orchestrator.key -Target (Resolve-Path orchestrator\storage\tls\orchestrator.key)
```

Manager chỉ cần tệp `.crt` để xác minh Orchestrator. Liên kết thêm `.key` là
tùy chọn, chủ yếu để giữ cặp TLS cục bộ đi cùng nhau.

Với Defender chạy thủ công, liên kết các tệp chứng chỉ theo đúng tên Defender:

```powershell
New-Item -ItemType Directory -Force manager\storage\tls\defenders
New-Item -ItemType SymbolicLink -Path manager\storage\tls\defenders\local-defender.crt -Target (Resolve-Path defender\storage\tls\local-defender.crt)
New-Item -ItemType SymbolicLink -Path manager\storage\tls\defenders\local-defender.key -Target (Resolve-Path defender\storage\tls\local-defender.key)
```

Thay `local-defender` bằng đúng giá trị `DEFENDER_NAME`. Manager xác minh
Defender bằng `{DEFENDER_SERVER_TLS_DIRECTORY}/{DEFENDER_NAME}.crt`; liên kết
`.key` là tùy chọn đối với Manager.

Trên Linux hoặc macOS, dùng symlink thư mục:

```sh
mkdir -p manager/storage/tls/orchestrator manager/storage/tls/defenders orchestrator/storage/tls defender/storage/tls
ln -sfn "$(pwd)/orchestrator/storage/tls" manager/storage/tls/orchestrator/shared
ln -sfn "$(pwd)/defender/storage/tls" manager/storage/tls/defenders/shared
```

Khi Defender được Orchestrator triển khai vào Docker, tệp TLS của Defender nằm
trong Docker volume có tên `defender_tls`. Manager chạy trực tiếp trên máy chủ
sẽ không đọc được volume đó; khi phát triển cục bộ, hãy chạy toàn bộ hệ thống
bằng Docker Compose hoặc đặt `DEFENDER_SERVER_TLS_SKIP_VERIFY=true`.

### 5. Dựng Defender để Orchestrator triển khai

Từ thư mục gốc của kho mã nguồn:

```powershell
docker build -t defly-defender:latest ./defender
docker volume create defender_tls
```

Volume chung `defender_tls` phải tồn tại trước khi Orchestrator triển khai
Defender. Các volume lỗi và nhật ký riêng của từng Defender sẽ được
Orchestrator tạo khi cần.

### 6. Chạy Defender thủ công

Thông thường Defender được tạo từ Manager thông qua Orchestrator. Nếu cần chạy
riêng để phát triển Defender, chạy trực tiếp từ thư mục `defender` sau khi cơ
sở dữ liệu đã được Manager migrate và seed.

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

Cổng mặc định của Defender:

- `9947`: API điều khiển Defender
- `9948`: proxy của Defender

## Các lỗi cài đặt thường gặp

- Docker không mở được cổng `80` hoặc `443`: đổi `MANAGER_HTTP_PORT` và
  `MANAGER_HTTPS_PORT`.
- Manager không gọi được Orchestrator: kiểm tra `ORCHESTRATOR_BASE_URL`, thông
  tin xác thực Orchestrator và cấu hình TLS.
- Orchestrator không triển khai được Defender: kiểm tra Docker API hoặc socket,
  `SERVER_DEFENDER_IMAGE`, mạng Compose và volume.
- Manager không xác minh được TLS của Defender: chạy toàn bộ hệ thống bằng
  Compose hoặc kiểm tra `DEFENDER_SERVER_TLS_DIRECTORY`.
