# Bắt đầu

Docker Compose là cách nhanh nhất để chạy thử toàn bộ Defly trên máy phát
triển.

## Yêu cầu tối thiểu

- Docker Engine hoặc Docker Desktop có Compose V2.
- Cổng `80` và `443` đang rảnh, hoặc đổi `MANAGER_HTTP_PORT` và
  `MANAGER_HTTPS_PORT` trong tệp `.env`.

## Chạy nhanh

Từ thư mục gốc của kho mã nguồn:

```powershell
Copy-Item .env.example .env
docker compose build defender
docker compose up -d --build
docker compose ps
```

Mở Manager tại:

```text
https://localhost/defly-manager
```

Nếu `USER_PASSWORD=random`, đọc mật khẩu người dùng đầu tiên bằng lệnh:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

## Kiểm tra lần đầu

1. Đăng nhập Manager bằng `USER_EMAIL` và mật khẩu đã cấu hình hoặc mật khẩu
   được tạo tự động.
2. Tạo mục tiêu hoặc ứng dụng phía sau cần bảo vệ.
3. Tạo bản ghi Defender với cổng proxy phù hợp.
4. Triển khai Defender từ Manager.
5. Gửi yêu cầu qua cổng proxy của Defender.
6. Kiểm tra quyết định, báo cáo và nhật ký trong Manager.

## Tạo Defender đầu tiên

Khi tạo Defender trong Manager, cần chọn mục tiêu cần bảo vệ và cổng proxy mà
Defender sẽ mở ra ngoài. Cổng này phải chưa bị dịch vụ khác sử dụng trên máy
chạy Docker.

Sau khi lưu bản ghi, dùng thao tác triển khai trong Manager. Manager sẽ đưa yêu
cầu vào hàng đợi, tiến trình hàng đợi sẽ gọi Orchestrator, rồi Orchestrator tạo
container Defender tương ứng.

## Kiểm tra proxy và WAF

Sau khi Defender chạy, gửi một yêu cầu HTTP hoặc HTTPS qua cổng proxy của
Defender. Nếu cấu hình đúng, Defender sẽ chuyển tiếp yêu cầu về ứng dụng phía
sau, đồng thời ghi lại kết quả xử lý trong phần quyết định, báo cáo hoặc nhật
ký.
