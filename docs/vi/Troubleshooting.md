# Khắc phục sự cố

Phần này liệt kê các lỗi thường gặp và hướng kiểm tra nhanh.

## Manager không kết nối được Orchestrator

Kiểm tra:

- `ORCHESTRATOR_BASE_URL` có đúng không
- tên máy hoặc DNS có phân giải được không
- `ORCHESTRATOR_USERNAME` và `ORCHESTRATOR_PASSWORD` có khớp không
- Orchestrator có cho phép máy gọi trong `SERVER_MANAGER` không
- cấu hình TLS và tệp `.crt` có đúng không
- tiến trình hàng đợi có đang chạy không

## Orchestrator không truy cập được Docker

Kiểm tra:

- `SERVER_DOCKER_BASE_URL` có đúng không
- Docker Desktop đã bật TCP Docker API chưa, nếu dùng `tcp://localhost:2375`
- tiến trình Orchestrator có quyền đọc `/var/run/docker.sock` không, nếu dùng
  Linux
- Orchestrator có chạy trong môi trường nhìn thấy Docker daemon không

## Không tìm thấy image Defender

Triệu chứng thường gặp là triển khai thất bại vì Docker không tìm thấy
`SERVER_DEFENDER_IMAGE`.

Cách xử lý:

```powershell
docker compose build defender
```

Hoặc dựng trực tiếp:

```powershell
docker build -t defly-defender:latest ./defender
```

Sau đó kiểm tra lại giá trị `SERVER_DEFENDER_IMAGE`.

## Xác minh chứng chỉ TLS thất bại

Kiểm tra:

- đường dẫn `.crt` trong `ORCHESTRATOR_TLS_CERT_FILE`
- thư mục trong `DEFENDER_SERVER_TLS_DIRECTORY`
- junction hoặc symlink có trỏ đúng thư mục không
- volume TLS có được gắn đúng khi chạy bằng Docker Compose không
- tên tệp chứng chỉ Defender có khớp với `DEFENDER_NAME` không

Khi phát triển cục bộ, có thể tạm đặt biến bỏ qua xác minh TLS. Không nên dùng
cách này cho môi trường vận hành thật.

## Kết nối cơ sở dữ liệu thất bại

Kiểm tra:

- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME` bên Orchestrator
- `DATABASE_HOST`, `DATABASE_PORT`, `DATABASE_NAME`, `DATABASE_USER`,
  `DATABASE_PASS` bên Defender
- MariaDB hoặc MySQL có đang chạy không
- migration của Manager đã chạy chưa

## Tác vụ hàng đợi bị kẹt

Kiểm tra:

- dịch vụ `worker` có đang chạy không
- `QUEUE_CONNECTION=database` có đúng không
- bảng hàng đợi trong cơ sở dữ liệu có tồn tại không
- nhật ký của `worker`
- nhật ký của Manager khi tạo tác vụ

Lệnh xem nhật ký:

```powershell
docker compose logs -f worker
```

## Cổng đã được sử dụng

Nếu Docker không mở được cổng `80` hoặc `443`, đổi:

```text
MANAGER_HTTP_PORT
MANAGER_HTTPS_PORT
```

Nếu Defender không mở được cổng proxy, đổi cổng trong bản ghi Defender rồi
triển khai lại.

## Lỗi quyền trên thư mục lưu trữ

Kiểm tra:

- volume có được gắn đúng không
- thư mục `storage` có quyền ghi không
- người dùng chạy container hoặc tiến trình có quyền ghi không
- tệp sinh ra có bị khóa bởi tiến trình khác không

## Defender không chuyển tiếp được về ứng dụng phía sau

Kiểm tra:

- `PROXY_BACKEND_URL` có đúng không
- Defender có cùng mạng với ứng dụng phía sau không
- ứng dụng phía sau có đang chạy không
- quy tắc WAF có đang chặn yêu cầu không
- nhật ký và quyết định của Defender ghi gì
