# Hướng dẫn Manager

Manager là ứng dụng Laravel/Filament dùng để quản trị Defly. Khi chạy bằng
Docker Compose, địa chỉ mặc định là:

```text
https://localhost/defly-manager
```

## Bảng điều khiển

Bảng điều khiển hiển thị trạng thái vận hành và các tín hiệu tổng quan của hệ
thống. Đây là nơi nên kiểm tra đầu tiên sau khi đăng nhập để biết Manager,
Orchestrator và các Defender có hoạt động bình thường hay không.

## Người dùng, nhóm và quyền

Manager quản lý truy cập theo ba lớp:

- Người dùng: tài khoản đăng nhập vào Manager.
- Nhóm: tập hợp người dùng có cùng vai trò vận hành.
- Quyền: khả năng xem, tạo, sửa, xóa hoặc thực hiện thao tác cụ thể.

Nên cấp quyền theo nhóm thay vì cấp trực tiếp cho từng người dùng, trừ trường
hợp cần ngoại lệ rõ ràng.

## Khóa API

Khóa API dùng cho các tích hợp gọi vào Manager API. Khi tạo khóa, cần giới hạn
quyền theo đúng mục đích sử dụng và thu hồi khóa không còn dùng nữa.

Mặc định Manager đọc khóa từ tiêu đề HTTP `X-Token-Key`, tùy theo cấu hình
`TOKEN_KEY_NAME`.

## Nhãn

Nhãn giúp phân loại dữ liệu trong Manager. Có thể dùng nhãn để nhóm mục tiêu,
Defender, quy tắc hoặc các đối tượng vận hành liên quan đến cùng một hệ thống.

## Mục tiêu

Mục tiêu mô tả ứng dụng phía sau hoặc tài nguyên cần bảo vệ. Khi tạo mục tiêu,
cần khai báo địa chỉ mà Defender sẽ chuyển tiếp yêu cầu đến.

Mỗi Defender thường gắn với một mục tiêu cụ thể, để việc theo dõi quyết định và
báo cáo rõ ràng hơn.

## Bộ máy xử lý

Bộ máy xử lý xác định cách Defly xử lý quy tắc WAF. Cấu hình này là nền tảng để
các quy tắc và nguyên tắc được áp dụng nhất quán khi Defender chạy.

## Mẫu khớp và danh sách từ

Mẫu khớp mô tả dữ liệu cần tìm trong yêu cầu hoặc phản hồi. Danh sách từ chứa
nhiều giá trị có thể được dùng lại trong mẫu khớp hoặc quy tắc.

Nên tách dữ liệu có thể tái sử dụng vào danh sách từ để tránh lặp cấu hình ở
nhiều quy tắc.

## Hành động

Hành động xác định Defender phải làm gì khi quy tắc khớp. Các hành động thường
gặp là cho qua, chặn hoặc ghi nhận để điều tra.

Khi thay đổi hành động của quy tắc đang áp dụng, cần kiểm tra tác động trước
khi triển khai lại Defender.

## Quy tắc

Quy tắc ghép mục tiêu, mẫu khớp và hành động thành điều kiện kiểm tra cụ thể.
Một quy tắc tốt nên có phạm vi rõ, tên dễ hiểu và hành động phù hợp với mức độ
rủi ro.

## Nguyên tắc

Nguyên tắc là bộ chính sách gồm nhiều quy tắc. Defender áp dụng nguyên tắc để
quyết định cách xử lý truy cập đi qua proxy.

Nên nhóm các quy tắc cùng mục đích vào một nguyên tắc, ví dụ bảo vệ đăng nhập,
lọc đầu vào hoặc ghi nhận hành vi đáng ngờ.

## Quyết định

Quyết định là kết quả xử lý từng yêu cầu hoặc phản hồi. Dữ liệu này cho biết
quy tắc nào đã khớp, hành động nào được áp dụng và Defender đã xử lý ra sao.

## Defender

Khu vực Defender dùng để khai báo tiến trình chạy, cổng proxy, mục tiêu và thao
tác triển khai. Các thao tác chính gồm:

- triển khai Defender
- hủy triển khai đang chạy
- theo dõi nhật ký triển khai
- kiểm tra trạng thái Defender

Trước khi triển khai, cần chắc chắn cổng proxy chưa bị dùng bởi dịch vụ khác.

## Báo cáo và dòng thời gian

Báo cáo ghi nhận dữ liệu phục vụ theo dõi và điều tra. Dòng thời gian giúp xem
lịch sử thay đổi hoặc sự kiện quan trọng trong hệ thống.

Khi điều tra sự cố, nên xem theo thứ tự: quyết định, báo cáo, nhật ký Defender,
rồi dòng thời gian thay đổi cấu hình.
