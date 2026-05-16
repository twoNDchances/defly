# Tham chiếu

Phần này gom các thông tin tra cứu nhanh khi làm việc với Defly.

## Cấu trúc thư mục

```text
defly/
  defender/       Chương trình Defender viết bằng Go
  docs/           Tài liệu
  manager/        Ứng dụng Manager viết bằng Laravel/Filament
  orchestrator/   Dịch vụ Orchestrator viết bằng Django ASGI
  workflows/      Tệp tham chiếu quy trình
  docker-compose.yml
  .env.example
```

## Cổng mặc định

- Manager HTTP: `80`
- Manager HTTPS: `443`
- Manager khi chạy thủ công: `8080`
- Orchestrator khi phát triển: `8000`
- API điều khiển Defender: `9947`
- Proxy Defender: `9948`

## Thông tin mặc định và đường dẫn

- Địa chỉ Manager khi chạy bằng Docker: `https://localhost/defly-manager`
- Tiền tố giao diện Manager: `defly-manager`
- Tiền tố API Manager: `v1`
- Tiền tố API Orchestrator: `api/v1`
- Người dùng Manager được seed mặc định: xem `USER_EMAIL` trong `.env`
- Mật khẩu đầu tiên: xem `USER_PASSWORD`, hoặc tệp thông tin xác thực nếu dùng
  `random`

## Nhóm biến môi trường quan trọng

Biến cơ sở dữ liệu chung:

```text
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

Biến Orchestrator:

```text
ORCHESTRATOR_BASE_URL
ORCHESTRATOR_USERNAME
ORCHESTRATOR_PASSWORD
SERVER_DOCKER_BASE_URL
SERVER_DEFENDER_IMAGE
SERVER_DEFENDER_TLS_VOLUME
```

Biến Defender:

```text
DATABASE_HOST
DATABASE_PORT
DATABASE_NAME
DATABASE_USER
DATABASE_PASS
DEFENDER_NAME
PROXY_BACKEND_URL
```

Biến TLS:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY
ORCHESTRATOR_TLS_CERT_FILE
DEFENDER_SERVER_TLS_SKIP_VERIFY
DEFENDER_SERVER_TLS_DIRECTORY
```

## Thuật ngữ

- Mục tiêu: ứng dụng phía sau hoặc tài nguyên cần bảo vệ.
- Bộ máy xử lý: cấu hình xử lý quy tắc.
- Mẫu khớp: mẫu dữ liệu dùng để so khớp.
- Danh sách từ: danh sách giá trị dùng trong mẫu khớp hoặc quy tắc.
- Hành động: việc Defender thực hiện khi quy tắc khớp.
- Quy tắc: điều kiện kiểm tra và hành động tương ứng.
- Nguyên tắc: bộ chính sách gồm nhiều quy tắc.
- Defender: chương trình áp dụng nguyên tắc và xử lý truy cập qua proxy.
- Quyết định: kết quả WAF cho một yêu cầu hoặc phản hồi.
- Báo cáo: dữ liệu ghi nhận để theo dõi và điều tra.
- Orchestrator: dịch vụ điều phối việc triển khai Defender qua Docker.
- Manager: giao diện và API quản trị của Defly.
