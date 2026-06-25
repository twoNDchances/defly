# Hướng dẫn Orchestrator

Orchestrator là ranh giới Django ASGI giữa Manager/Worker và Docker. Trang này mô tả hành vi triển khai Docker. Hình dạng endpoint nằm trong [Tham chiếu API](API-Reference.md), còn biến nằm trong [Biến môi trường](Environment-Variables.md).

## Trách nhiệm

Orchestrator:

- xác thực caller nội bộ và xác định người thực hiện;
- kiểm tra action Defender trước mọi Docker side effect;
- tạo, thay thế, follow và xóa container Defender;
- gán image, environment, port, network, volume và Compose label;
- ghi deployment state/detail vào database dùng chung.

Nó không sở hữu migration database, semantics WAF hay các nút lifecycle
trong Manager.

## Vòng đời deployment

Manager ghi nhận ý định và Worker thực hiện queued call. Sau đó Orchestrator:

1. xác thực Basic Auth và host caller được phép;
2. xác định email người thực hiện và quyền Defender cần thiết;
3. khóa/nạp deployment state của Defender;
4. đọc Docker Compose context nếu đang chạy trong Compose;
5. tạo, start, thay thế hoặc xóa container;
6. lưu `successful` hoặc `failed` cùng detail có thể xử lý.

State model thuộc [Defender](CoreConcepts/Defender.md). Ownership queue và retry thuộc
[Vận hành](Operations.md).

## Compose context

Defender động nhận project label, network membership hiện tại và service label riêng.
Nhờ vậy Compose nhận nó là thành viên cùng project mà không nhầm với Orchestrator. Bên
ngoài Compose không có project context để suy ra và Docker dùng network mặc định.

Khi Defender không tới được MariaDB hoặc backend, hãy kiểm tra network/DNS container
trước khi điều tra policy.

## Tài nguyên container

- Image lấy từ tên Defender image đã cấu hình.
- Proxy port lấy từ bản ghi Defender và publish trên host.
- TLS dùng volume Defender TLS chung.
- Log và error dùng volume riêng theo Defender.
- Environment được ghép từ cấu hình hệ thống và bản ghi Defender.

Thay container không đồng nghĩa xóa dữ liệu bền vững. Tên volume, mặc định và key cấu
hình thuộc [Biến môi trường](Environment-Variables.md).

## Bảo mật Docker

Docker access tương đương quyền quản trị host. Ưu tiên Unix socket local hoặc endpoint
được bảo vệ đúng cách; không mở Docker TCP API không xác thực. Giới hạn container,
credential và network Orchestrator theo [Bảo mật](Security.md#tiến-trình-nền-docker).

## Xác thực và phân quyền

Transport authentication và caller allowlist chạy trước handler. Deployment middleware
ánh xạ method của route thành `deploy`, `follow` hoặc `cancel`, rồi kiểm tra action đó
trên model Defender cho người thực hiện. Thiếu identity, permission hoặc contract khớp
sẽ fail trước khi gọi Docker.

Header, method và status response chính xác thuộc
[Tham chiếu API](API-Reference.md#orchestrator-api). Mapping credential giữa service
thuộc [Cấu hình](Configuration.md#manager-và-orchestrator).

## Follow và cancel

Follow trả output container cho Manager. Cancel dừng/xóa deployment và cập nhật state.
Cả hai bắt đầu từ job Worker; nếu Manager có vẻ bị kẹt, kiểm tra queue trước khi retry
Docker action.

Lệnh chẩn đoán và thứ tự triệu chứng nằm trong [Khắc phục sự cố](Troubleshooting.md).
