# Bắt đầu nhanh

Trang này đưa toàn bộ hệ thống lên bằng Docker Compose. Để hiểu vai trò từng dịch vụ, đọc [Tổng quan](Overview.md) trước.

## Yêu cầu

- Docker Engine hoặc Docker Desktop có Compose V2.
- Cổng `80` và `443` đang rảnh, hoặc đổi cổng Manager trong `.env`.
- Máy chủ Docker có đủ tài nguyên để chạy MariaDB, Manager, Worker và Orchestrator.

## Khởi động

Từ thư mục gốc:

```powershell
Copy-Item .env.example .env
docker compose build defender
docker compose up -d --build
docker compose ps
```

Image Defender cần được dựng trước vì [Orchestrator](Orchestrator-Guide.md) dùng image này để triển khai container động.

Mở Manager tại:

```text
https://localhost/defly-manager
```

Nếu `USER_PASSWORD=random`, đọc thông tin xác thực khởi tạo:

```powershell
docker compose exec manager sh -lc "cat /var/www/html/credentials.txt"
```

Trình duyệt có thể cảnh báo chứng chỉ tự ký trong môi trường cục bộ.

## Kiểm tra dịch vụ

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

Chỉ tiếp tục khi Manager truy cập được, Worker đang chạy và Orchestrator kết nối được Docker.

## Tạo chính sách tối thiểu

Trước khi thao tác, đọc tuyến [Target](CoreConcepts/Target.md) -> [Engine](CoreConcepts/Engine.md) -> [Rule](CoreConcepts/Rule.md) -> [Action](CoreConcepts/Action.md) -> [Principle](CoreConcepts/Principle.md) -> [Decision](CoreConcepts/Decision.md).

Một chính sách thử nghiệm nên bắt đầu bằng ghi nhật ký hoặc tạo báo cáo thay vì chặn:

1. Chọn một Pattern hoặc tạo Target đọc rõ một trường trong yêu cầu.
2. Tạo Rule với phép so sánh phù hợp kiểu dữ liệu của Target.
3. Gắn Action `log` hoặc `report` vào Rule.
4. Tạo Principle cùng giai đoạn và thêm Rule.
5. Kiểm tra Principle cho đến khi trạng thái là `passed`.

## Tạo Defender đầu tiên

1. Tạo bản ghi [Defender](CoreConcepts/Defender.md) với tên và cổng proxy chưa được sử dụng.
2. Cấu hình URL máy chủ phía sau trong nhóm biến proxy.
3. Áp dụng Principle và cài đặt Decision cần thiết.
4. Dùng thao tác triển khai trong Manager.
5. Theo dõi `deployment_status` và nhật ký triển khai.

## Kiểm tra proxy

Gửi yêu cầu qua cổng proxy của Defender, không gửi trực tiếp tới máy chủ phía sau. Sau đó kiểm tra:

- Máy chủ phía sau có nhận yêu cầu không.
- Nhật ký Defender có lỗi không.
- [Report](CoreConcepts/Report.md) có được tạo khi hành động tương ứng chạy không.
- Điểm và Decision có đúng kỳ vọng không.

Nếu triển khai thất bại, chuyển tới [Khắc phục sự cố](Troubleshooting.md). Nếu cần cấu hình chi tiết, đọc [Cài đặt](Installation.md) và [Cấu hình](Configuration.md).
