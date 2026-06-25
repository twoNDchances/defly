# Cài đặt

Defly có hai cách chạy:

- Docker Compose cho toàn bộ hệ thống, phù hợp để đánh giá và vận hành trên một máy chủ.
- Cài thủ công từng dịch vụ, phù hợp khi phát triển.

Đọc [Kiến trúc](Architecture.md) nếu cần hiểu vì sao Manager, Worker, Orchestrator và Defender phải dùng chung một số tài nguyên.

## Docker Compose

### Yêu cầu

- Docker Engine hoặc Docker Desktop có Compose V2.
- Cổng Manager và các cổng proxy của Defender chưa bị sử dụng.
- Tiến trình Docker cho phép Orchestrator tạo container động.

### 1. Tạo cấu hình

```powershell
Copy-Item .env.example .env
```

Trước khi chạy, đổi tối thiểu:

```text
MARIADB_ROOT_PASSWORD
DB_PASSWORD
ORCHESTRATOR_PASSWORD
APP_URL
USER_EMAIL
USER_PASSWORD
```

Ý nghĩa đầy đủ nằm tại [Biến môi trường](Environment-Variables.md#env-gốc-của-docker-compose).

### 2. Dựng image Defender

[Orchestrator](Orchestrator-Guide.md) triển khai Defender từ `SERVER_DEFENDER_IMAGE`:

```powershell
docker compose build defender
```

Tên image phải khớp giá trị trong `.env`, mặc định là `defly-defender:latest`.

### 3. Khởi động hệ thống

```powershell
docker compose up -d --build
docker compose ps
```

Theo dõi khởi động:

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

### 4. Đăng nhập Manager

Địa chỉ mặc định:

```text
https://localhost/defly-manager
```

Nếu `USER_PASSWORD=random`:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

Manager có thể dùng chứng chỉ tự ký ở môi trường cục bộ, nên trình duyệt sẽ yêu cầu xác nhận lần đầu.

### 5. Kiểm tra tài nguyên Compose

Hệ thống tạo mạng:

```text
${COMPOSE_PROJECT_NAME}_infrastructure
```

Các dịch vụ tĩnh và Defender do Orchestrator triển khai đều gắn vào mạng này. Container Defender động nhận các nhãn Compose của dự án hiện tại, vì vậy `docker compose down` có thể nhận diện và dừng cùng dự án.

Các ổ dữ liệu cơ sở dữ liệu, lưu trữ, TLS, nhật ký và lỗi tồn tại độc lập với vòng đời container. Không dùng `docker compose down -v` trừ khi muốn xóa dữ liệu.

### 6. Tạo Defender

Sau khi đăng nhập, làm theo [Bắt đầu nhanh](Getting-Started.md#4-tạo-defender-đầu-tiên). Khái niệm cấu hình một Defender được giải thích tại [Defender](CoreConcepts/Defender.md).

## Cài thủ công

Cách này chạy các dịch vụ từ mã nguồn nhưng vẫn có thể dùng Docker cho MariaDB và container Defender.

### Yêu cầu phát triển

- PHP `8.3+`, Composer `2` và các phần mở rộng Laravel cần thiết.
- Node.js cùng npm cho tài nguyên Manager.
- Python `3.14+` và `uv` cho Orchestrator.
- Go `1.26.1+` cho Defender.
- MariaDB hoặc MySQL.
- Docker nếu cần Orchestrator triển khai Defender.

### 1. Cơ sở dữ liệu

Tạo một cơ sở dữ liệu dùng chung:

```sql
CREATE DATABASE defly_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'defly'@'%' IDENTIFIED BY 'change_me_app';
GRANT ALL PRIVILEGES ON defly_manager.* TO 'defly'@'%';
FLUSH PRIVILEGES;
```

Manager sở hữu migration. Luôn chạy migration của Manager trước khi chạy Orchestrator hoặc Defender.

### 2. Manager và Worker

```powershell
cd manager
Copy-Item .env.example .env
composer install
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```

Cấu hình cơ sở dữ liệu và Orchestrator trong `manager/.env`, sau đó chạy:

```powershell
php artisan serve --host=127.0.0.1 --port=8080
```

Ở terminal khác:

```powershell
cd manager
php artisan queue:work --tries=3
```

Manager cục bộ có địa chỉ `http://127.0.0.1:8080/defly-manager`.

### 3. Orchestrator

```powershell
cd orchestrator
Copy-Item .env.example .env
uv sync
uv run python manage.py generatesecretkeyfile
$env:DJANGO_SETTINGS_MODULE = "configs.development"
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

`SERVER_DOCKER_BASE_URL` phải trỏ tới tiến trình Docker mà Orchestrator truy cập được. Trên Linux có thể dùng `unix:///var/run/docker.sock`; TCP `2375` chỉ nên dùng trên máy phát triển tin cậy.

### 4. Defender

Thông thường Defender được Orchestrator tạo. Để chạy trực tiếp:

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

Cổng mặc định là `9947` cho API điều khiển và `9948` cho proxy.

### 5. TLS khi chạy thủ công

Manager phải đọc được chứng chỉ của Orchestrator và Defender nếu không bỏ qua bước xác minh. Cách đơn giản nhất trong môi trường cục bộ là dùng đường dẫn lưu trữ chung hoặc tạo liên kết thư mục.

Các biến liên quan:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY
ORCHESTRATOR_TLS_CERT_FILE
DEFENDER_SERVER_TLS_SKIP_VERIFY
DEFENDER_SERVER_TLS_DIRECTORY
```

Không liên kết khóa bí mật vào vị trí không cần thiết. Manager chỉ cần chứng chỉ `.crt` để xác minh phía bên kia. Xem thêm [Bảo mật](Security.md#tls-giữa-các-dịch-vụ).

## Xác nhận sau cài đặt

1. Manager truy cập và đăng nhập được.
2. Worker lấy được tác vụ.
3. Orchestrator kết nối được Docker.
4. Image Defender tồn tại.
5. Defender kết nối được cơ sở dữ liệu và máy chủ phía sau.
6. Yêu cầu qua proxy tạo kết quả WAF mong đợi.

Nếu một bước thất bại, xem [Khắc phục sự cố](Troubleshooting.md).
