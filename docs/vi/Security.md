# Bảo mật

Defly quản lý chính sách bảo vệ ứng dụng web, nên cấu hình bảo mật cần được
kiểm tra kỹ trước khi vận hành thật.

## Mô hình xác thực

Manager là nơi quản lý người dùng, nhóm và quyền. Người dùng đăng nhập vào
Manager để cấu hình mục tiêu, quy tắc, nguyên tắc và Defender.

API của Manager dùng khóa API theo cấu hình `TOKEN_KEY_NAME`, mặc định là:

```text
X-Token-Key
```

Manager hoặc tiến trình hàng đợi gọi Orchestrator bằng Basic Auth.

## Mô hình phân quyền

Nên cấp quyền theo nhóm, tránh cấp quyền trực tiếp cho từng người dùng nếu
không cần thiết. Các quyền nhạy cảm như triển khai Defender, hủy triển khai,
quản lý khóa API và quản lý người dùng nên được giới hạn cho nhóm vận hành phù
hợp.

## TLS giữa các dịch vụ

Manager có thể xác minh TLS khi gọi Orchestrator và Defender.

Các biến quan trọng:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY
ORCHESTRATOR_TLS_CERT_FILE
DEFENDER_SERVER_TLS_SKIP_VERIFY
DEFENDER_SERVER_TLS_DIRECTORY
```

Trong môi trường vận hành thật, nên xác minh TLS bằng chứng chỉ tin cậy thay vì
bỏ qua xác minh.

## Rủi ro Docker socket và Docker API

Docker socket và Docker API có quyền rất mạnh. Nếu kẻ tấn công truy cập được,
họ có thể tạo container, đọc volume hoặc thay đổi hệ thống.

Khuyến nghị:

- không mở TCP Docker API ra mạng công khai
- chỉ bật TCP Docker API trên máy phát triển cục bộ đáng tin cậy
- giới hạn mạng có thể truy cập Orchestrator
- bảo vệ máy chạy Orchestrator như một thành phần có đặc quyền cao

## Dữ liệu nhạy cảm

Không đưa các dữ liệu sau vào kho mã nguồn:

- tệp `.env` thật
- mật khẩu thật
- khóa API thật
- khóa riêng TLS
- tệp bí mật được sinh ra
- dữ liệu sao lưu cơ sở dữ liệu

## Danh sách kiểm tra cho môi trường vận hành thật

- Đặt `APP_DEBUG=false`.
- Đặt `APP_KEY` ổn định và không chia sẻ công khai.
- Đặt mật khẩu cơ sở dữ liệu đủ mạnh.
- Đặt mật khẩu Orchestrator đủ mạnh.
- Xác minh TLS giữa Manager, Orchestrator và Defender.
- Hạn chế đường mạng tới Docker API.
- Xoay vòng khóa API và mật khẩu theo lịch.
- Sao lưu cơ sở dữ liệu định kỳ.
- Kiểm tra quyền người dùng và nhóm sau mỗi lần thay đổi nhân sự.
