# Tài liệu tham chiếu API

Phần này mô tả các nhóm API chính của Defly. Chi tiết từng yêu cầu và phản hồi
cần được bổ sung thêm khi giao kèo API ổn định.

## Manager API

Manager API phục vụ các tích hợp gọi vào hệ thống quản trị. Tiền tố mặc định:

```text
v1
```

Khóa API mặc định được gửi qua tiêu đề HTTP:

```text
X-Token-Key
```

Các giá trị này có thể đổi bằng `API_PREFIX` và `TOKEN_KEY_NAME` trong cấu hình
Manager.

## Orchestrator API

Orchestrator API nhận yêu cầu từ Manager hoặc tiến trình hàng đợi. Tiền tố mặc
định:

```text
api/v1
```

Tài nguyên triển khai mặc định:

```text
deployments
```

Orchestrator dùng Basic Auth với thông tin xác thực lấy từ:

```text
ORCHESTRATOR_USERNAME
ORCHESTRATOR_PASSWORD
SERVER_USERNAME
SERVER_PASSWORD
```

Các thao tác chính:

- triển khai Defender
- theo dõi nhật ký
- hủy triển khai

## Defender API

Defender API là API điều khiển của từng Defender. Manager dùng API này để kiểm
tra hoặc điều khiển Defender sau khi triển khai.

API này nên được xem là API nội bộ. Không nên mở ra mạng công khai nếu chưa có
lớp bảo vệ phù hợp.

## Xác thực

Defly dùng nhiều cơ chế xác thực tùy đường đi:

- Người dùng đăng nhập vào Manager qua cơ chế xác thực của Laravel/Filament.
- Tích hợp gọi Manager API bằng khóa API.
- Manager hoặc tiến trình hàng đợi gọi Orchestrator bằng Basic Auth.
- Manager gọi Defender qua API điều khiển, có thể kèm xác minh TLS tùy cấu
  hình.

## Tiêu đề HTTP thường dùng

Các tiêu đề HTTP quan trọng:

```text
X-Token-Key
Authorization
```

Orchestrator cũng có thể nhận thêm tiêu đề HTTP chứa email người thực hiện, tùy cấu
hình `SERVER_EMAIL_HEADER_KEY`.

## Ví dụ cần bổ sung

Các ví dụ sau nên được bổ sung khi API ổn định:

- yêu cầu triển khai Defender
- phản hồi triển khai thành công
- phản hồi triển khai thất bại
- yêu cầu theo dõi nhật ký
- yêu cầu hủy triển khai
- định dạng lỗi chung

## Mã trạng thái

Các mã trạng thái cần quy ước rõ:

- yêu cầu hợp lệ và xử lý thành công
- yêu cầu hợp lệ nhưng tác vụ đang chạy
- dữ liệu gửi lên không hợp lệ
- xác thực thất bại
- không có quyền truy cập
- tài nguyên không tồn tại
- lỗi nội bộ khi triển khai hoặc gọi Docker
