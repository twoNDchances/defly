# Hướng dẫn Manager

Manager là giao diện Laravel/Filament để quản trị truy cập, xây dựng chính sách, triển khai [Defender](CoreConcepts/Defender.md) và điều tra [Report](CoreConcepts/Report.md).

Địa chỉ mặc định khi chạy bằng Compose:

```text
https://localhost/defly-manager
```

## Bắt đầu từ bảng điều khiển

Bảng điều khiển dùng để xem thông tin tổng quan trước khi thay đổi cấu hình: trạng thái Defender, tình hình triển khai, báo cáo gần đây và xu hướng. Biểu đồ có bộ lọc thời gian hoặc phạm vi khi dữ liệu hỗ trợ.

Bảng điều khiển không thay thế nhật ký. Khi thấy trạng thái bất thường, hãy mở Defender, chi tiết triển khai hoặc báo cáo liên quan.

## Quản trị truy cập

Đọc các khái niệm theo thứ tự:

1. [User](CoreConcepts/User.md)
2. [Group](CoreConcepts/Group.md)
3. [Permission](CoreConcepts/Permission.md)
4. [Key](CoreConcepts/Key.md)

Nên cấp quyền theo nhóm. Chỉ cấp trực tiếp cho người dùng hoặc khóa khi có ngoại lệ rõ ràng. Khóa API cần có thời hạn, mục đích và người sở hữu cụ thể.

## Phân loại tài nguyên

[Label](CoreConcepts/Label.md) giúp nhóm cấu hình theo ứng dụng, môi trường hoặc nhóm phụ trách. Label không thay đổi quá trình chạy WAF và không thay thế quyền.

## Xây dựng chính sách

Không bắt đầu từ Principle. Hãy tạo thành phần theo hướng từ dữ liệu đến phán quyết.

### 1. Dữ liệu tái sử dụng

- [Wordlist](CoreConcepts/Wordlist.md) cho danh sách khóa, giá trị hoặc biểu thức chính quy.
- [Pattern](CoreConcepts/Pattern.md) cho bộ trích xuất tích hợp sẵn mà Defender đã hỗ trợ.

Pattern hệ thống thường bị khóa vì phải đồng bộ với mã nguồn Defender.

### 2. Target

[Target](CoreConcepts/Target.md) chọn giai đoạn, loại và kiểu dữ liệu. Nếu dùng Pattern, Pattern quyết định kiểu dữ liệu. Nếu Target là mảng và không có Pattern, cần Wordlist chứa tên trường.

Sau khi chọn Target, có thể gắn [Engine](CoreConcepts/Engine.md) theo thứ tự để chuẩn hóa dữ liệu. Kiểm tra kiểu dữ liệu đầu vào/đầu ra của từng bước.

### 3. Rule

[Rule](CoreConcepts/Rule.md) kết hợp Target, phép so sánh, giá trị đối chiếu hoặc Wordlist. Phép so sánh phải phù hợp với kiểu dữ liệu sau chuỗi Engine.

Đặt tên Rule theo điều kiện, không theo hành động. Ví dụ `request-body-has-password-field` rõ hơn `deny-bad-request`.

### 4. Action

[Action](CoreConcepts/Action.md) mô tả tác động khi Rule khớp. Trong giai đoạn thử nghiệm, ưu tiên `log`, `report` hoặc `suspect` trước `deny`.

`allow` và `deny` dừng các hành động phía sau, nên thứ tự gắn có ý nghĩa.

### 5. Principle

[Principle](CoreConcepts/Principle.md) nhóm các Rule cùng giai đoạn bằng AND. Chọn cấp độ theo độ nghiêm ngặt, sắp thứ tự Rule và chạy kiểm tra hợp lệ.

Không áp dụng Principle có trạng thái `pending`, `validating` hoặc `failed` cho Defender. Sau khi sửa thành phần phụ thuộc, hãy kiểm tra lại.

### 6. Decision

[Decision](CoreConcepts/Decision.md) so sánh điểm và áp dụng hành động cuối theo hướng. Sắp Decision cụ thể trước Decision tổng quát; kiểm tra hành động có phù hợp với yêu cầu/phản hồi không.

## Tạo và triển khai Defender

Trong biểu mẫu Defender:

1. Đặt tên ổn định.
2. Chọn cổng proxy chưa sử dụng.
3. Cấu hình URL máy chủ phía sau và các biến môi trường cần thiết.
4. Áp dụng Principle đã có trạng thái `passed`.
5. Cài Decision đúng hướng.
6. Lưu rồi chạy triển khai.

Manager tạo tác vụ; Worker gọi [Orchestrator](Orchestrator-Guide.md). Theo dõi `deployment_status`, `deployment_details` và nhật ký. `successful` chỉ mô tả kết quả triển khai, còn `status` mô tả sức khỏe trong quá trình chạy.

## Cập nhật chính sách đang chạy

Khi thay Target, Engine, Action hoặc Rule dùng chung:

1. Xác định tất cả Principle phụ thuộc.
2. Kiểm tra lại Principle.
3. Kiểm tra Defender đang áp dụng Principle đó.
4. Triển khai hoặc đồng bộ lại theo quy trình hiện tại.
5. Gửi yêu cầu thử và kiểm tra báo cáo/nhật ký.

Không đổi trực tiếp chính sách trên môi trường thật khi chưa có bước quan sát trước bằng nhật ký/báo cáo.

## Điều tra

Đọc dữ liệu theo thứ tự:

1. [Report](CoreConcepts/Report.md): yêu cầu nào, Rule nào và giá trị nào khớp.
2. Nhật ký Defender: quá trình chạy đã đi đến đâu và gặp lỗi gì.
3. Chi tiết triển khai: container có đúng image, mạng và biến môi trường không.
4. [Timeline](CoreConcepts/Timeline.md): ai vừa thay đổi chính sách hoặc Defender.

## Thao tác nhạy cảm

Quản lý người dùng, khóa, triển khai, hủy Defender và sửa Decision chặn cần quyền riêng. Xem [Bảo mật](Security.md) trước khi phân quyền cho môi trường thật.
