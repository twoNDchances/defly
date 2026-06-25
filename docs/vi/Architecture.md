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

Ở mức kiến trúc, dữ liệu chính sách đi theo thứ tự:

```text
Pattern/Wordlist -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

Đây là thứ tự thực thi/đọc hiểu, không khẳng định mọi model đứng cạnh nhau đều có
quan hệ database trực tiếp. Ý nghĩa, compatibility và persisted relation của model
thuộc [Khái niệm cốt lõi](CoreConcepts/README.md).

## Vòng đời HTTP

Defender đánh giá ba phase request, áp dụng Decision hướng request, chuyển traffic
được phép tới backend, rồi đánh giá ba phase response và Decision hướng response trước
khi trả dữ liệu. Cách trích xuất từng phase thuộc
[Target](CoreConcepts/Target.md#sáu-giai-đoạn-http); thứ tự runtime thuộc
[Hướng dẫn Defender](Defender-Guide.md).

## Luồng triển khai

Manager tạo tác vụ nền. Worker gọi Orchestrator kèm danh tính người thực hiện,
Orchestrator cấp quyền cho lifecycle action được yêu cầu rồi mới được phép thay đổi
trạng thái Docker. Orchestrator tạo hoặc xóa container Defender và trả trạng thái kết
quả cho Manager. Nhãn container, mạng, ổ dữ liệu, cổng và cơ chế dọn dẹp thuộc trách
nhiệm của [Orchestrator](Orchestrator-Guide.md#vòng-đời-deployment).

## Cơ sở dữ liệu

Manager, Orchestrator và Defender dùng chung cơ sở dữ liệu nhưng không có quyền sở hữu ngang nhau. Migration chỉ chạy từ Manager. Orchestrator và Defender phải tương thích với lược đồ hiện tại.

## TLS và ranh giới tin cậy

Manager có thể xác minh TLS khi gọi Orchestrator và API điều khiển Defender. Orchestrator có quyền cao vì truy cập tiến trình Docker. Xem [Bảo mật](Security.md) trước khi mở Docker API hoặc API điều khiển ra ngoài máy chủ tin cậy.

## Hàng đợi

Các thao tác triển khai, hủy và theo dõi nhật ký có thể kéo dài nên đi qua Worker. Nếu giao diện đã tạo yêu cầu nhưng trạng thái không thay đổi, hãy kiểm tra Worker trước Orchestrator; xem [Khắc phục sự cố](Troubleshooting.md#tác-vụ-hàng-đợi-không-chạy).
