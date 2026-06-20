# Khắc phục sự cố

Chẩn đoán theo tuyến [Manager](Manager-Guide.md) -> Worker -> [Orchestrator](Orchestrator-Guide.md) -> Docker -> [Defender](CoreConcepts/Defender.md) -> máy chủ phía sau -> chính sách. Dừng tại lớp đầu tiên bị lỗi.

## Manager không gọi được Orchestrator

Kiểm tra:

- `ORCHESTRATOR_BASE_URL` và DNS.
- Basic Auth hai phía có khớp không.
- Bên gọi có nằm trong `SERVER_MANAGER` không.
- Đường dẫn/phương thức triển khai, theo dõi, hủy có giống nhau không.
- Chứng chỉ TLS và `ORCHESTRATOR_TLS_SKIP_VERIFY`.
- Worker có đang chạy không.

Xem nhật ký:

```powershell
docker compose logs -f manager worker orchestrator
```

## Tác vụ hàng đợi không chạy

Triệu chứng: Manager đã nhận thao tác nhưng trạng thái triển khai không đổi.

```powershell
docker compose ps worker
docker compose logs -f worker
```

Kiểm tra `QUEUE_CONNECTION`, bảng hàng đợi, thời gian chờ/số lần thử và kết nối cơ sở dữ liệu. Khởi động lại Worker sau khi đổi biến môi trường hoặc mã nguồn tác vụ.

## Orchestrator không truy cập Docker

Kiểm tra `SERVER_DOCKER_BASE_URL`, quyền socket và khả năng truy cập tiến trình Docker từ môi trường chạy Orchestrator.

Với Docker Desktop, TCP `2375` phải được bật nếu cấu hình dùng TCP. Chỉ dùng cách này trên máy phát triển tin cậy.

## Không tìm thấy image Defender

```powershell
docker compose build defender
docker image inspect defly-defender:latest
```

Tên image được kiểm tra phải khớp `SERVER_DEFENDER_IMAGE`.

## Defender không thuộc dự án Compose

Kiểm tra nhãn và mạng:

```powershell
docker inspect <defender-container>
```

Container cần có `com.docker.compose.project`, `com.docker.compose.service`, `com.docker.compose.config-hash` và mạng `${COMPOSE_PROJECT_NAME}_infrastructure`. Hãy triển khai lại Defender sau khi nâng cấp Orchestrator có logic nhận diện ngữ cảnh Compose.

## Cổng bị sử dụng

Nếu Manager không mở được cổng `80/443`, đổi `MANAGER_HTTP_PORT` hoặc `MANAGER_HTTPS_PORT`.

Nếu Defender triển khai lỗi do cổng, đổi `proxy_port` trong bản ghi [Defender](CoreConcepts/Defender.md), rồi triển khai lại. Không dùng cùng cổng máy chủ cho hai Defender.

## Kết nối cơ sở dữ liệu thất bại

Đối chiếu ba bộ tên biến:

- Manager: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- Orchestrator: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- Defender: `DATABASE_HOST`, `DATABASE_PORT`, `DATABASE_NAME`, `DATABASE_USER`, `DATABASE_PASS`.

Chạy migration từ Manager. Không tạo lược đồ riêng từ Orchestrator hoặc Defender.

## Xác minh TLS thất bại

Kiểm tra chứng chỉ tồn tại, chưa hết hạn, đúng tên máy chủ và bên gọi đọc được. Với Defender, tên tệp phải khớp tên Defender mà Manager dùng để tìm chứng chỉ.

Chỉ tạm bỏ qua xác minh để phân biệt lỗi tin cậy trong môi trường cục bộ; sau đó sửa chứng chỉ thay vì giữ cấu hình không an toàn.

## Defender không gọi được máy chủ phía sau

Kiểm tra theo thứ tự:

1. `PROXY_BACKEND_URL`.
2. Máy chủ phía sau đang lắng nghe đúng địa chỉ/cổng.
3. DNS bên trong container.
4. Defender và máy chủ phía sau có mạng chung hoặc tuyến phù hợp.
5. Thời gian chờ/TLS của proxy.
6. Chính sách có chặn/hủy yêu cầu không.

## Yêu cầu bị chặn ngoài ý muốn

1. Xem [Report](CoreConcepts/Report.md) và chi tiết Rule.
2. Xác định giá trị Target sau chuỗi Engine.
3. Kiểm tra phép so sánh, cờ đảo kết quả và Wordlist.
4. Kiểm tra toàn bộ Rule trong [Principle](CoreConcepts/Principle.md).
5. Kiểm tra Action nào cộng điểm.
6. Kiểm tra [Decision](CoreConcepts/Decision.md) nào khớp điểm.
7. Dùng [Timeline](CoreConcepts/Timeline.md) để tìm thay đổi gần nhất.

Tạm chuyển hành động chặn sang ghi nhật ký/tạo báo cáo hoặc thu hồi Principle nếu cần khôi phục lưu lượng.

## Không có Report

Report chỉ được tạo khi Action `report` thực sự chạy. Kiểm tra Rule/Principle có khớp, Action có nằm sau `allow`/`deny` không, chuỗi kết nối cơ sở dữ liệu của Defender và nhật ký lỗi tạo báo cáo.

## Lỗi quyền vùng lưu trữ

Kiểm tra đường gắn, người sở hữu và quyền ghi của `storage/errors`, `storage/logs`, `storage/requests`, tệp Wordlist và TLS. Việc tạo lại container không sửa được quyền sai trong ổ dữ liệu.
