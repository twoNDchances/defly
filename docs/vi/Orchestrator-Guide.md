# Hướng dẫn Orchestrator

Orchestrator là dịch vụ Django ASGI nội bộ của Manager. Nó tồn tại để gom các việc không nên chạy trực tiếp trong Laravel: điều khiển Docker và gọi nhà cung cấp AI.

## Trách nhiệm

- Nhận yêu cầu nội bộ từ Manager/Worker bằng Basic Auth.
- Đọc email người thực hiện từ header đã cấu hình.
- Kiểm tra quyền trước khi triển khai Defender hoặc trả lời trợ lý AI.
- Tạo, thay thế, theo dõi log và xóa container Defender.
- Gọi nhà cung cấp AI cho trang trợ lý của Manager.

Orchestrator không sở hữu migration, không quản lý giao diện và không thực thi WAF.

## Triển khai Defender

Manager tạo tác vụ, Worker gọi Orchestrator, rồi Orchestrator kiểm tra quyền `deploy`, `follow` hoặc `cancel` trên Defender. Nếu hợp lệ, nó đọc bản ghi Defender, chuẩn hóa `environment_variables`, lấy image đã cấu hình, gán port, network, volume, Compose label và cập nhật `deployment_status`/`deployment_details` vào cơ sở dữ liệu chung.

Defender động dùng chung ngữ cảnh Compose với Orchestrator để `docker compose down` có thể dọn cùng dự án. Nếu Defender không tới được MariaDB hoặc máy chủ phía sau, kiểm tra network/DNS trước khi kiểm tra chính sách.

## Trợ lý AI

Manager lưu hội thoại và tài nguyên đính kèm trong cơ sở dữ liệu, sau đó gọi endpoint assistant của Orchestrator. Orchestrator kiểm tra quyền `Conservation:chat`, kiểm tra quyền xem các tài nguyên đính kèm, giới hạn số tin nhắn/ký tự theo cấu hình rồi gọi nhà cung cấp AI bằng nhóm biến `AI_*`.

Orchestrator chỉ trả nội dung phản hồi cho Manager; Manager vẫn là nơi lưu hội thoại và hiển thị giao diện.

## Bảo mật và cấu hình

Docker access tương đương quyền quản trị host, nên không mở Docker TCP API không xác thực. Các đường dẫn, phương thức, Basic Auth, header email, TLS và biến AI phải khớp giữa Manager và Orchestrator; xem [Cấu hình](Configuration.md), [Biến môi trường](Environment-Variables.md) và [Tham chiếu API](API-Reference.md#orchestrator-api).
