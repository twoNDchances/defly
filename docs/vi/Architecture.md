# Kiến trúc

Defly tách quản trị, điều phối và xử lý lưu lượng thành các dịch vụ độc lập. [Manager](Manager-Guide.md) không điều khiển Docker trực tiếp; [Defender](CoreConcepts/Defender.md) không sở hữu lược đồ cơ sở dữ liệu.

## Sơ đồ hệ thống

```text
Quản trị viên
    |
    v
Manager UI/API ----> MariaDB
    |
    v
Laravel Queue ----> Worker ----Basic Auth----> Orchestrator ----> Docker API
                                                        |
                                                        v
Máy khách -> Defender proxy ----> Máy chủ đích     Container Defender
                  |
                  +---- đọc chính sách / ghi báo cáo -> MariaDB
```

## Quyền sở hữu

| Thành phần | Sở hữu |
| --- | --- |
| Manager | Lược đồ, migration, dữ liệu khởi tạo, giao diện/API và chính sách. |
| Worker | Thực thi tác vụ nền do Manager tạo. |
| Orchestrator | Vòng đời container Defender, mạng, ổ dữ liệu và ánh xạ cổng. |
| Defender | Giao dịch HTTP, trạng thái điểm/cấp độ, nhật ký và việc thực thi WAF. |
| MariaDB | Lưu dữ liệu chung, nhưng lược đồ do Manager quản lý. |

## Chuỗi xử lý chính sách

Chính sách đi theo thứ tự:

```text
Pattern/Wordlist -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

- [Pattern](CoreConcepts/Pattern.md) và [Wordlist](CoreConcepts/Wordlist.md) cung cấp dữ liệu tái sử dụng.
- [Target](CoreConcepts/Target.md) chọn dữ liệu HTTP.
- [Engine](CoreConcepts/Engine.md) biến đổi giá trị.
- [Rule](CoreConcepts/Rule.md) so sánh.
- [Action](CoreConcepts/Action.md) cập nhật giao dịch HTTP hoặc tạo tác động.
- [Principle](CoreConcepts/Principle.md) kết hợp các quy tắc bằng AND rồi điều phối hành động.
- [Decision](CoreConcepts/Decision.md) đưa ra phán quyết theo điểm.

## Vòng đời HTTP

Defender thu thập yêu cầu trước khi chạy từng giai đoạn:

1. Toàn bộ yêu cầu.
2. Tiêu đề HTTP, tham số truy vấn và siêu dữ liệu của yêu cầu.
3. Nội dung và tệp của yêu cầu.

Sau các Principle của yêu cầu, Decision hướng yêu cầu được chạy. Nếu không bị `deny` hoặc `cancel`, yêu cầu được chuyển tiếp đến máy chủ phía sau.

Khi máy chủ phía sau trả dữ liệu, Defender chạy:

4. Tiêu đề HTTP và siêu dữ liệu của phản hồi.
5. Nội dung phản hồi.
6. Toàn bộ phản hồi.

Cuối cùng, Decision hướng phản hồi được áp dụng trước khi trả cho máy khách. Chi tiết giai đoạn và loại nằm tại [Target](CoreConcepts/Target.md#sáu-giai-đoạn-http).

## Luồng triển khai

Manager tạo tác vụ nền. Worker gọi Orchestrator bằng thông tin xác thực và thông tin người dùng thực hiện. Orchestrator kiểm tra yêu cầu, dùng Docker API để tạo container, gắn mạng `defly_infrastructure`, các ổ dữ liệu TLS/nhật ký/lỗi và cập nhật trạng thái.

Container động được gắn nhãn dự án/cấu hình Compose để lệnh `docker compose down` của dự án có thể nhận diện và dừng cùng hệ thống.

## Cơ sở dữ liệu

Manager, Orchestrator và Defender dùng chung cơ sở dữ liệu nhưng không có quyền sở hữu ngang nhau. Migration chỉ chạy từ Manager. Orchestrator và Defender phải tương thích với lược đồ hiện tại.

## TLS và ranh giới tin cậy

Manager có thể xác minh TLS khi gọi Orchestrator và API điều khiển Defender. Orchestrator có quyền cao vì truy cập tiến trình Docker. Xem [Bảo mật](Security.md) trước khi mở Docker API hoặc API điều khiển ra ngoài máy chủ tin cậy.

## Hàng đợi

Các thao tác triển khai, hủy và theo dõi nhật ký có thể kéo dài nên đi qua Worker. Nếu giao diện đã tạo yêu cầu nhưng trạng thái không thay đổi, hãy kiểm tra Worker trước Orchestrator; xem [Khắc phục sự cố](Troubleshooting.md#tác-vụ-hàng-đợi-không-chạy).
