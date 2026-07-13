# Tổng quan

Defly là tường lửa ứng dụng web gồm nhiều dịch vụ. [Manager](Manager-Guide.md) quản trị dữ liệu và giao diện, [Orchestrator](Orchestrator-Guide.md) xử lý tác vụ nội bộ cần quyền riêng như Docker và AI, còn [Defender](CoreConcepts/Defender.md) thực thi chính sách trên lưu lượng thật.

## Defly giải quyết vấn đề gì

Defly hỗ trợ:

- Quản lý người dùng, quyền và khóa API cho hệ thống bảo mật.
- Xây dựng chính sách từ dữ liệu HTTP đến hành động bảo vệ.
- Triển khai nhiều WAF cho nhiều máy chủ phía sau.
- Theo dõi báo cáo, trạng thái và lịch sử thay đổi.
- Chạy toàn bộ hệ thống bằng Docker Compose hoặc phát triển từng dịch vụ độc lập.

Nếu chưa biết cấu trúc chính sách, xem [Các khái niệm cốt lõi](CoreConcepts/README.md).

## Thành phần

| Dịch vụ | Trách nhiệm |
| --- | --- |
| [Manager](Manager-Guide.md) | Giao diện Laravel/Filament và API quản trị cấu hình, quyền, chính sách, Defender và báo cáo. |
| Worker | Xử lý hàng đợi Laravel cho các thao tác như triển khai, hủy và theo dõi nhật ký. |
| [Orchestrator](Orchestrator-Guide.md) | Dịch vụ Django nội bộ nhận lệnh từ Manager/Worker để triển khai Defender bằng Docker và gọi nhà cung cấp AI cho trợ lý. |
| [Defender](CoreConcepts/Defender.md) | Proxy ngược và WAF thực thi chính sách trên yêu cầu/phản hồi HTTP. |
| MariaDB | Cơ sở dữ liệu chung có lược đồ do Manager sở hữu; các dịch vụ đọc hoặc ghi theo trách nhiệm. |

[Defender](CoreConcepts/Defender.md) là cả khái niệm cấu hình trong Manager và tiến trình Go chạy thực tế.

## Các luồng chính

### Luồng cấu hình và triển khai

1. [User](CoreConcepts/User.md) tạo chính sách và bản ghi Defender trong Manager.
2. Manager lưu dữ liệu vào MariaDB.
3. Worker gửi yêu cầu nội bộ đã xác thực tới Orchestrator.
4. Orchestrator tạo container Defender trên máy chủ Docker.
5. Defender đọc chính sách đã được áp dụng từ cơ sở dữ liệu.

### Luồng lưu lượng

1. Máy khách gửi yêu cầu tới cổng proxy của Defender.
2. Defender xử lý ba giai đoạn của yêu cầu.
3. Các [Principle](CoreConcepts/Principle.md) đánh giá quy tắc và chạy hành động.
4. Các [Decision](CoreConcepts/Decision.md) hướng yêu cầu được xét theo điểm.
5. Nếu được phép, yêu cầu đi đến máy chủ phía sau.
6. Phản hồi quay lại qua ba giai đoạn phản hồi và Decision hướng phản hồi.
7. Defender trả dữ liệu cho máy khách và có thể tạo [Report](CoreConcepts/Report.md).

## Nên đọc tiếp gì

- Muốn chạy thử: [Bắt đầu nhanh](Getting-Started.md).
- Muốn hiểu hệ thống: [Kiến trúc](Architecture.md).
- Muốn tạo chính sách: [Các khái niệm cốt lõi](CoreConcepts/README.md).
- Muốn triển khai thật: [Cài đặt](Installation.md), [Cấu hình](Configuration.md) và [Bảo mật](Security.md).
