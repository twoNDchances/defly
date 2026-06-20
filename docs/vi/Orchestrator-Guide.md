# Hướng dẫn Orchestrator

Orchestrator là dịch vụ Django nhận yêu cầu từ Manager/Worker và điều khiển Docker để triển khai [Defender](CoreConcepts/Defender.md).

## Trách nhiệm

- Xác thực Basic Auth và máy chủ gọi.
- Đọc bản ghi Defender cùng cấu hình triển khai.
- Tạo, thay thế hoặc gỡ container.
- Gắn mạng, ổ dữ liệu, cổng và biến môi trường.
- Theo dõi nhật ký container.
- Cập nhật trạng thái và lỗi về cơ sở dữ liệu.

Orchestrator không tạo migration và không quyết định chính sách WAF.

## Vòng đời triển khai

Trạng thái thường đi qua:

```text
pending -> processing -> successful
					  -> failed
```

Luồng:

1. Manager tạo tác vụ.
2. Worker gọi điểm cuối triển khai.
3. Orchestrator xác thực bên gọi và tải Defender.
4. Dịch vụ Docker xác định ngữ cảnh Compose hiện tại từ container Orchestrator.
5. Container Defender được tạo với image, biến môi trường, ổ dữ liệu, mạng và nhãn Compose.
6. Trạng thái cùng thông tin chi tiết được ghi lại.

## Nhận diện Docker Compose

Defender động kế thừa các nhãn Compose quan trọng như dự án, dịch vụ, mã băm cấu hình, thư mục làm việc và tệp cấu hình. Nhờ đó Docker Compose nhận container là thành viên của dự án hiện tại và `docker compose down` có thể dừng cùng hệ thống.

Nhãn dịch vụ của Defender được tạo riêng để tránh trùng với Orchestrator, trong khi nhãn dự án vẫn giữ cùng dự án.

## Mạng

Orchestrator đọc mạng của container hiện tại và gắn Defender vào các mạng tương ứng. Trong hệ thống mặc định, mạng chính là:

```text
${COMPOSE_PROJECT_NAME}_infrastructure
```

Nếu Defender không tới được cơ sở dữ liệu hoặc máy chủ phía sau, hãy kiểm tra kết nối mạng trước khi kiểm tra chính sách WAF.

## Ổ dữ liệu

- TLS dùng ổ dữ liệu chung từ `SERVER_DEFENDER_TLS_VOLUME`.
- Nhật ký và lỗi dùng ổ dữ liệu riêng cho từng Defender.
- Tên ổ dữ liệu Compose có thể có tiền tố từ `COMPOSE_PROJECT_NAME`.

Không xóa ổ dữ liệu khi chỉ muốn triển khai lại container.

## Docker API

`SERVER_DOCKER_BASE_URL` có thể là TCP hoặc Unix socket:

```text
tcp://localhost:2375
unix:///var/run/docker.sock
```

Quyền Docker gần tương đương quyền quản trị cao nhất trên máy chủ. Không mở TCP Docker API ra mạng không tin cậy; xem [Bảo mật](Security.md#tiến-trình-nền-docker).

## Xác thực API

Manager dùng `ORCHESTRATOR_USERNAME` và `ORCHESTRATOR_PASSWORD`. Orchestrator dùng `SERVER_USERNAME` và `SERVER_PASSWORD`. Hai cặp phải khớp.

`SERVER_MANAGER` giới hạn bên gọi. Tiêu đề HTTP chứa email do `SERVER_EMAIL_HEADER_KEY` xác định và dùng để kiểm tra lịch sử thao tác.

## Theo dõi nhật ký và hủy

Theo dõi nhật ký sẽ đọc đầu ra của container để Manager hiển thị. Hủy sẽ dừng/gỡ Defender tương ứng và cập nhật trạng thái triển khai. Cả hai thao tác đi qua Worker nên cần kiểm tra hàng đợi nếu giao diện không cập nhật.

## Khi triển khai thất bại

Kiểm tra theo thứ tự:

1. Worker có gọi được Orchestrator không.
2. Thông tin xác thực và danh sách bên gọi được phép có đúng không.
3. Orchestrator có truy cập Docker không.
4. `SERVER_DEFENDER_IMAGE` có tồn tại không.
5. Cổng proxy có trùng không.
6. Mạng và ổ dữ liệu có tồn tại và có quyền phù hợp không.
7. Defender có kết nối được cơ sở dữ liệu và máy chủ phía sau không.

Chi tiết lệnh kiểm tra nằm tại [Khắc phục sự cố](Troubleshooting.md).
