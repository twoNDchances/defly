# Cấu hình

Defly dùng nhiều tệp `.env` vì mỗi dịch vụ có trách nhiệm riêng. Khi chạy bằng
Docker Compose, tệp `.env` ở thư mục gốc là nguồn cấu hình chính. Khi chạy thủ
công, từng dịch vụ cũng cần tệp `.env` riêng.

## Tệp `.env` ở thư mục gốc

Tệp `.env` ở thư mục gốc được Docker Compose dùng để cấu hình toàn bộ hệ
thống. Các nhóm biến quan trọng:

- Docker và image: `COMPOSE_PROJECT_NAME`, `MANAGER_IMAGE`,
  `ORCHESTRATOR_IMAGE`, `SERVER_DEFENDER_IMAGE`.
- Cơ sở dữ liệu: `MARIADB_VERSION`, `MARIADB_ROOT_PASSWORD`, `DB_DATABASE`,
  `DB_USERNAME`, `DB_PASSWORD`.
- Môi trường chạy Manager: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`,
  `APP_URL`, `APP_LOCALE`, `MANAGER_HTTP_PORT`, `MANAGER_HTTPS_PORT`.
- Người dùng khởi tạo: `USER_NAME`, `USER_EMAIL`, `USER_PASSWORD`.
- Giao kèo giữa Manager và Orchestrator: `ORCHESTRATOR_PATH_PREFIX`,
  `ORCHESTRATOR_PATH_DEPLOYMENT`, `ORCHESTRATOR_METHOD_DEPLOY`,
  `ORCHESTRATOR_METHOD_FOLLOW`, `ORCHESTRATOR_METHOD_CANCEL`,
  `ORCHESTRATOR_USERNAME`, `ORCHESTRATOR_PASSWORD`.
- Triển khai Defender: `SERVER_DEFENDER_TLS_VOLUME`,
  `DEFENDER_SERVER_TLS_SKIP_VERIFY`.
- Tiến trình hàng đợi: `WORKER_TRIES`, `WORKER_TIMEOUT`, `WORKER_MAX_TIME`.

## Tệp `manager/.env`

`manager/.env` cấu hình ứng dụng Laravel, cơ sở dữ liệu, thư điện tử, tiền tố
API, kết nối tới Orchestrator và cách xác minh TLS của Defender.

Các biến thường cần đổi khi chạy thủ công:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
ORCHESTRATOR_BASE_URL
ORCHESTRATOR_USERNAME
ORCHESTRATOR_PASSWORD
ORCHESTRATOR_TLS_SKIP_VERIFY
ORCHESTRATOR_TLS_CERT_FILE
DEFENDER_SERVER_TLS_SKIP_VERIFY
DEFENDER_SERVER_TLS_DIRECTORY
```

Khi `ORCHESTRATOR_TLS_SKIP_VERIFY=false`, Manager cần đọc được tệp chứng chỉ
được khai báo trong `ORCHESTRATOR_TLS_CERT_FILE`.

Khi `DEFENDER_SERVER_TLS_SKIP_VERIFY=false`, Manager cần đọc được thư mục chứng
chỉ Defender trong `DEFENDER_SERVER_TLS_DIRECTORY`.

## Tệp `orchestrator/.env`

`orchestrator/.env` cấu hình Django, cơ sở dữ liệu, Docker API và giao kèo API
mà Manager sẽ gọi.

Các biến chính:

```text
SECRET_KEY_FILE
ALLOWED_HOSTS
DB_HOST
DB_PORT
DB_USER
DB_PASS
DB_NAME
SERVER_MANAGER
SERVER_USERNAME
SERVER_PASSWORD
SERVER_EMAIL_HEADER_KEY
SERVER_PATH_PREFIX
SERVER_PATH_DEPLOYMENT
SERVER_METHOD_DEPLOY
SERVER_METHOD_FOLLOW
SERVER_METHOD_CANCEL
SERVER_DEFENDER_IMAGE
SERVER_DEFENDER_TLS_VOLUME
SERVER_DOCKER_BASE_URL
```

`SERVER_MANAGER` là danh sách máy được phép gọi Orchestrator.
`SERVER_USERNAME` và `SERVER_PASSWORD` phải khớp với cấu hình bên Manager.

## Biến môi trường của Defender

Khi chạy thủ công, Defender cần thông tin cơ sở dữ liệu, tên Defender và địa chỉ
ứng dụng phía sau:

```text
DATABASE_HOST
DATABASE_PORT
DATABASE_NAME
DATABASE_USER
DATABASE_PASS
DEFENDER_NAME
PROXY_BACKEND_URL
```

Khi triển khai bằng Orchestrator, các giá trị này được dựng từ bản ghi Defender,
cơ sở dữ liệu chung và cấu hình triển khai.

## Thư điện tử và Resend

Manager dùng các biến sau để gửi thư:

```text
MAIL_MAILER
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
RESEND_API_KEY
RESEND_DOMAIN
RESEND_PATH
```

Nếu có webhook của Resend, cần cấu hình thêm khóa bí mật tương ứng trong
Manager.

## Mã truy cập API

Manager API dùng các biến sau để xác định cách đọc mã truy cập:

```text
TOKEN_LOCATION
TOKEN_KEY_NAME
USER_AGENT
API_PREFIX
GUI_PREFIX
```

Giá trị mặc định của `TOKEN_KEY_NAME` là `X-Token-Key`. Giá trị mặc định của
`API_PREFIX` là `v1`.

## TLS, cổng, volume và mạng

- TLS từ Manager tới Orchestrator dùng `ORCHESTRATOR_TLS_SKIP_VERIFY` và
  `ORCHESTRATOR_TLS_CERT_FILE`.
- TLS từ Manager tới Defender dùng `DEFENDER_SERVER_TLS_SKIP_VERIFY` và
  `DEFENDER_SERVER_TLS_DIRECTORY`.
- Các cổng chính của Compose là `MANAGER_HTTP_PORT`, `MANAGER_HTTPS_PORT` và
  cổng proxy trong từng bản ghi Defender.
- TLS của Defender dùng volume chung `SERVER_DEFENDER_TLS_VOLUME`.
- Lỗi và nhật ký của Defender dùng volume riêng cho từng Defender, do
  Orchestrator tạo khi triển khai.
