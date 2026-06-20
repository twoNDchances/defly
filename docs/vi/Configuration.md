# Cấu hình

Defly dùng một `.env` gốc cho [Docker Compose](Installation.md#docker-compose) và `.env` riêng cho từng dịch vụ khi chạy thủ công. Không đưa bí mật thật vào kho mã nguồn. Danh sách đầy đủ, giá trị mặc định và ràng buộc của từng biến nằm tại [Biến môi trường](Environment-Variables.md).

## Cấu hình Docker Compose

Tệp `.env` ở thư mục gốc điều khiển image, cơ sở dữ liệu, cổng, thông tin xác thực và các biến được truyền vào dịch vụ.

### Dự án và image

| Biến | Ý nghĩa |
| --- | --- |
| `COMPOSE_PROJECT_NAME` | Tiền tố mạng, ổ dữ liệu và nhãn Compose; mặc định `defly`. |
| `MANAGER_IMAGE` | Image Manager. |
| `ORCHESTRATOR_IMAGE` | Image Orchestrator. |
| `SERVER_DEFENDER_IMAGE` | Image dùng để tạo [Defender](CoreConcepts/Defender.md). |

Mạng chính có tên `${COMPOSE_PROJECT_NAME}_infrastructure`.

### Cơ sở dữ liệu

Các dịch vụ phải trỏ tới cùng cơ sở dữ liệu:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
MARIADB_ROOT_PASSWORD
```

Manager dùng `DB_USERNAME`/`DB_PASSWORD`; Compose ánh xạ sang tên biến phù hợp cho Orchestrator và Defender.

### Manager và người dùng khởi tạo

```text
APP_NAME
APP_ENV
APP_KEY
APP_DEBUG
APP_URL
APP_LOCALE
MANAGER_HTTP_PORT
MANAGER_HTTPS_PORT
USER_NAME
USER_EMAIL
USER_PASSWORD
```

Trong môi trường thật, đặt `APP_DEBUG=false` và dùng `APP_KEY` ổn định.

### Worker

```text
WORKER_TRIES
WORKER_TIMEOUT
WORKER_MAX_TIME
```

Worker xử lý triển khai, hủy và theo dõi nhật ký. Thời gian chờ phải đủ dài để Docker tải image, dựng và khởi động container nhưng không nên che giấu tác vụ bị treo.

## Manager

`manager/.env` cấu hình Laravel, cơ sở dữ liệu, thư điện tử, API và phần gọi Orchestrator/Defender.

### Kết nối tới Orchestrator

```text
ORCHESTRATOR_BASE_URL
ORCHESTRATOR_USERNAME
ORCHESTRATOR_PASSWORD
ORCHESTRATOR_TLS_SKIP_VERIFY
ORCHESTRATOR_TLS_CERT_FILE
ORCHESTRATOR_PATH_PREFIX
ORCHESTRATOR_PATH_DEPLOYMENT
ORCHESTRATOR_METHOD_DEPLOY
ORCHESTRATOR_METHOD_FOLLOW
ORCHESTRATOR_METHOD_CANCEL
```

Tên đăng nhập/mật khẩu phải khớp `SERVER_USERNAME`/`SERVER_PASSWORD` phía Orchestrator. Đường dẫn và phương thức ở hai bên cũng phải mô tả cùng một giao kèo API.

### Kết nối tới Defender

```text
DEFENDER_SERVER_TLS_SKIP_VERIFY
DEFENDER_SERVER_TLS_DIRECTORY
```

Khi bật xác minh, Manager tìm chứng chỉ theo tên Defender trong thư mục đã cấu hình.

### Manager API

```text
TOKEN_LOCATION
TOKEN_KEY_NAME
USER_AGENT
API_PREFIX
GUI_PREFIX
```

Mặc định tiền tố API là `v1`, tiền tố giao diện là `defly-manager` và tiêu đề HTTP chứa [Key](CoreConcepts/Key.md) là `X-Token-Key`.

### Thư điện tử

```text
MAIL_MAILER
MAIL_FROM_ADDRESS
MAIL_FROM_NAME
RESEND_API_KEY
RESEND_DOMAIN
RESEND_PATH
```

Chỉ cấu hình Resend khi dùng mailer tương ứng. Webhook cần secret riêng trong Manager.

## Orchestrator

`orchestrator/.env` cấu hình Django, cơ sở dữ liệu, Docker và API nhận từ Manager.

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

`SERVER_MANAGER` giới hạn máy chủ được phép gọi. `SERVER_DOCKER_BASE_URL` trao quyền điều khiển Docker nên cần được bảo vệ như thông tin xác thực đặc quyền.

## Defender

Defender có ba nhóm cấu hình: dùng chung, máy chủ điều khiển và proxy. Khi Orchestrator triển khai, các giá trị được dựng từ bản ghi Defender cùng cấu hình hệ thống.

### Biến dùng chung

```text
DATABASE_HOST
DATABASE_PORT
DATABASE_NAME
DATABASE_USER
DATABASE_PASS
DEFENDER_NAME
```

Ngoài cơ sở dữ liệu còn có đường dẫn lưu trữ lỗi, Wordlist và dữ liệu trong quá trình chạy.

### Máy chủ điều khiển

Máy chủ này cung cấp API điều khiển, mặc định ở cổng `9947`. Nhóm biến này cấu hình địa chỉ, TLS, ghi nhật ký và Manager tin cậy. `SERVER_SECURITY_MANAGER` mặc định là `worker` để các tác vụ quản lý đi qua đúng tiến trình.

### Proxy

```text
PROXY_BACKEND_URL
```

Proxy mặc định ở cổng `9948`, nhận lưu lượng ứng dụng và chuyển tiếp tới máy chủ phía sau. Các biến khác điều khiển TLS, thời gian chờ, ghi nhật ký, theo dõi sức khỏe và điểm theo mức độ nghiêm trọng.

Điểm `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency` được [Action](CoreConcepts/Action.md#suspect) `suspect` sử dụng.

## TLS, ổ dữ liệu và mạng

- TLS Defender dùng ổ dữ liệu `${COMPOSE_PROJECT_NAME}_${SERVER_DEFENDER_TLS_VOLUME}`.
- Nhật ký và lỗi dùng ổ dữ liệu riêng theo Defender.
- Tất cả dịch vụ Compose và Defender động dùng mạng `${COMPOSE_PROJECT_NAME}_infrastructure`.
- Cổng proxy lấy từ bản ghi Defender và được công bố trên máy chủ Docker.

## Kiểm tra sau khi đổi cấu hình

1. So sánh biến giao kèo ở cả hai phía Manager và Orchestrator.
2. Chạy `docker compose config` để xem cấu hình Compose sau khi tổng hợp.
3. Khởi động lại dịch vụ bị ảnh hưởng.
4. Triển khai lại Defender nếu biến môi trường hoặc ổ dữ liệu thay đổi.
5. Kiểm tra nhật ký và tình trạng sức khỏe trước khi gửi lưu lượng thật.
