# Kiến trúc

Defly tách trách nhiệm thành nhiều dịch vụ để Manager không trực tiếp điều
khiển Docker, và Defender có thể chạy độc lập theo từng mục tiêu cần bảo vệ.

## Sơ đồ hệ thống

```text
Trình duyệt hoặc quản trị viên
    |
    v
Manager UI/API -- hàng đợi --> Worker
    |                          |
    |                          v
    |                    Orchestrator -- Docker API --> Container Defender
    |                          |
    v                          v
MariaDB <------------------- Defender
                               |
Máy khách cần bảo vệ -> Proxy Defender -> Ứng dụng phía sau
```

## Quyền sở hữu dữ liệu

- Manager sở hữu lược đồ cơ sở dữ liệu và migration chính.
- Orchestrator đọc và ghi các bảng cần cho vòng đời triển khai.
- Defender đọc cấu hình khi chạy và ghi quyết định, báo cáo, nhật ký liên quan
  đến WAF.

## Luồng Manager đến Orchestrator

Manager không gọi Docker trực tiếp. Khi người dùng yêu cầu triển khai, Manager
tạo tác vụ nền. Tiến trình hàng đợi lấy tác vụ đó và gọi điểm truy cập triển
khai của Orchestrator bằng Basic Auth.

Yêu cầu gửi sang Orchestrator có kèm email của người thực hiện để phục vụ nhật
ký kiểm toán và hiển thị trong Manager.

## Luồng Orchestrator đến Docker

Orchestrator dùng Docker API để:

- tạo container Defender
- gắn container vào mạng Compose phù hợp
- gắn volume TLS, lỗi và nhật ký
- mở cổng proxy đã cấu hình
- cập nhật trạng thái triển khai về cơ sở dữ liệu

Orchestrator phải truy cập được Docker daemon được khai báo trong
`SERVER_DOCKER_BASE_URL`.

## Luồng Manager đến API điều khiển Defender

Sau khi Defender được triển khai, Manager có thể gọi API điều khiển của Defender
để kiểm tra hoặc điều khiển tiến trình chạy. Việc xác minh TLS phụ thuộc vào
`DEFENDER_SERVER_TLS_SKIP_VERIFY` và thư mục chứng chỉ trong
`DEFENDER_SERVER_TLS_DIRECTORY`.

## Luồng proxy của Defender

Khi truy cập đi qua proxy của Defender:

1. Defender nhận yêu cầu từ máy khách.
2. Bộ máy WAF chạy các pha xử lý yêu cầu.
3. Quy tắc được khớp với dữ liệu trong yêu cầu.
4. Hành động tương ứng được áp dụng, ví dụ cho qua, chặn hoặc ghi nhật ký.
5. Nếu yêu cầu được cho qua, Defender chuyển tiếp tới ứng dụng phía sau.
6. Phản hồi đi ngược lại qua Defender và có thể tiếp tục được xử lý.
7. Defender ghi quyết định và báo cáo để Manager hiển thị.

## Chia sẻ chứng chỉ TLS

Orchestrator và Defender có thể tạo chứng chỉ TLS. Manager cần đọc được tệp
`.crt` tương ứng nếu cấu hình yêu cầu xác minh TLS.

Khi chạy bằng Docker Compose, các volume TLS đã được gắn sẵn. Khi chạy thủ công,
cần tạo junction hoặc symlink để Manager đọc được chứng chỉ từ thư mục của
Orchestrator hoặc Defender.

## Tiến trình hàng đợi

Các thao tác có thể mất thời gian, như triển khai Defender hoặc theo dõi nhật
ký, được đẩy sang tiến trình hàng đợi. Cách này giúp giao diện và API của
Manager không phải chờ trực tiếp trong khi Orchestrator đang làm việc với
Docker.
