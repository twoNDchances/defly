# Vận hành

Trang này dành cho hệ thống Docker Compose. Đọc [Kiến trúc](Architecture.md) và [Cấu hình](Configuration.md) trước khi thay đổi mạng, ổ dữ liệu hoặc thông tin xác thực.

## Khởi động và dừng

```powershell
docker compose up -d --build
docker compose ps
```

Khởi động lại dịch vụ tĩnh:

```powershell
docker compose restart manager worker orchestrator
```

Dừng dự án nhưng giữ ổ dữ liệu:

```powershell
docker compose down
```

[Defender](CoreConcepts/Defender.md) do Orchestrator tạo mang các nhãn Compose của dự án hiện tại, nên `docker compose down` có thể nhận diện và dừng cùng hệ thống.

Xóa cả ổ dữ liệu có tên:

```powershell
docker compose down -v
```

Lệnh cuối xóa cơ sở dữ liệu và các ổ lưu trữ. Chỉ chạy sau khi đã sao lưu và xác nhận đúng dự án.

## Xem nhật ký

```powershell
docker compose logs -f mariadb manager worker orchestrator
```

Có thể xem nhật ký Defender động qua thao tác theo dõi trong Manager hoặc qua Docker:

```powershell
docker ps --filter "label=com.docker.compose.project=defly"
docker logs -f <defender-container>
```

Nếu đổi `COMPOSE_PROJECT_NAME`, hãy thay điều kiện lọc tương ứng.

## Kiểm tra sức khỏe theo từng lớp

Kiểm tra theo thứ tự để tránh chẩn đoán sai:

1. MariaDB hoạt động bình thường và nhận kết nối.
2. Giao diện/API Manager trả phản hồi.
3. Worker đang lấy tác vụ.
4. Orchestrator gọi được Docker.
5. Defender có trạng thái triển khai `successful` và sức khỏe `normal`.
6. Proxy Defender gọi được máy chủ phía sau.
7. Chính sách tạo nhật ký/báo cáo đúng kỳ vọng.

Không kiểm tra Rule trước khi mạng hoặc máy chủ phía sau hoạt động.

## Sao lưu

Cơ sở dữ liệu chứa chính sách, người dùng, trạng thái triển khai, báo cáo và lịch sử thao tác. Nơi lưu trữ có thể chứa tệp Wordlist, TLS, nhật ký, lỗi và yêu cầu nguyên bản.

Trước khi nâng cấp hoặc xóa ổ dữ liệu:

1. Dump MariaDB.
2. Sao lưu các ổ dữ liệu cần giữ.
3. Ghi lại thẻ phiên bản image và `.env` đang dùng.
4. Kiểm tra khả năng khôi phục trên môi trường riêng.

Khi khôi phục, Manager, Orchestrator và Defender phải cùng trỏ tới cơ sở dữ liệu đã khôi phục, đồng thời lược đồ phải tương thích với image.

## Nâng cấp

1. Đọc thay đổi `.env.example` của cả ba dịch vụ.
2. Sao lưu cơ sở dữ liệu và vùng lưu trữ.
3. Tải/dựng image mới.
4. Chạy migration Manager.
5. Khởi động dịch vụ tĩnh.
6. Dựng lại image Defender.
7. Triển khai lại từng Defender có kiểm soát.
8. Gửi yêu cầu kiểm tra nhanh và kiểm tra [Report](CoreConcepts/Report.md).

```powershell
docker compose build defender
docker compose up -d --build
```

## Xoay vòng thông tin xác thực

Các thông tin xác thực cần lịch xoay vòng:

- Mật khẩu cơ sở dữ liệu.
- Basic Auth Manager-Orchestrator.
- [Key](CoreConcepts/Key.md) của Manager API.
- Mật khẩu người dùng quản trị.
- Chứng chỉ TLS/khóa bí mật.

Khi đổi thông tin xác thực giữa hai dịch vụ, hãy cập nhật cả hai phía trước khi khởi động lại để giảm thời gian gián đoạn.

## TLS

Khi thay chứng chỉ:

1. Giữ đúng tên tệp mà bên gọi tìm kiếm.
2. Kiểm tra ổ dữ liệu hoặc đường dẫn gắn vào container.
3. Kiểm tra quyền đọc chứng chỉ và quyền bảo vệ khóa bí mật.
4. Khởi động lại dịch vụ máy chủ.
5. Xác nhận bên gọi không cần `skip_verify`.

## Mở rộng số lượng Worker

```powershell
docker compose up -d --scale worker=3
```

Theo dõi độ dài hàng đợi, tải cơ sở dữ liệu và các tác vụ triển khai trùng nhau. Tăng số Worker không làm máy chủ Docker có thêm tài nguyên.

## Lưu giữ dữ liệu WAF

[Report](CoreConcepts/Report.md), nhật ký và yêu cầu nguyên bản có thể tăng nhanh và chứa dữ liệu nhạy cảm. Cần chính sách lưu giữ, giới hạn quyền đọc và quy trình xóa phù hợp. Không dựa vào việc tạo lại container để dọn ổ dữ liệu.
