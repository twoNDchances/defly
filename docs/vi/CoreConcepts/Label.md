# Label

Label là siêu dữ liệu màu dùng để phân loại và lọc tài nguyên trong Manager. Label không tham gia logic tường lửa, không thay đổi quyền và không được Defender nạp để xử lý HTTP.

## Các trường cấu hình

| Trường | Bắt buộc | Ràng buộc |
| --- | --- | --- |
| `name` | Có | Duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `color` | Có | Mã màu hex hợp lệ. |
| `description` | Không | Giải thích quy ước sử dụng label. |

Label có UUID, `created_by` và thời điểm tạo/cập nhật.

## Tài nguyên hỗ trợ

Label dùng quan hệ đa hình `labels_resources` và hiện hỗ trợ:

- [User](User.md)
- [Group](Group.md)
- [Permission](Permission.md)
- [Wordlist](Wordlist.md)
- [Engine](Engine.md)
- [Target](Target.md)
- [Action](Action.md)
- [Rule](Rule.md)
- [Principle](Principle.md)
- [Decision](Decision.md)
- [Defender](Defender.md)

Pattern, Key, Report và Timeline không có quan hệ Label trong mô hình dữ liệu hiện tại.

## Cách sử dụng

Một tài nguyên có thể có nhiều Label và một Label có thể gắn nhiều loại tài nguyên. Một số quy ước hữu ích:

- Môi trường: `production`, `staging`, `development`.
- Mức độ quản trị: `critical`, `experimental`, `legacy`.
- Nhóm chính sách: `authentication`, `upload`, `api-abuse`.
- Trạng thái nghiệp vụ bổ sung: `needs-review`, `approved`.

Không nên dùng Label để mô phỏng trạng thái đã có trường riêng, ví dụ `validation_status`, `deployment_status`, `is_applied` hoặc `is_implemented`, vì Label không được quy trình cập nhật tự động.

## Label không thay thế Permission

Gắn Label `production` vào Defender không hạn chế ai được triển khai. Gắn Label `approved` vào Principle không thay thế trạng thái kiểm tra `passed`. Mọi kiểm tra truy cập vẫn do [Permission](Permission.md), lớp chính sách, trạng thái khóa và trạng thái bản ghi quyết định.

## Lịch sử thao tác

Tạo, sửa và xóa Label được ghi vào [Timeline](Timeline.md). Quan hệ Label phục vụ tìm kiếm/trình bày và không được đưa vào báo cáo tường lửa.

## Danh sách kiểm tra

- Xây dựng bộ tên Label nhất quán trước khi dùng rộng rãi.
- Dùng màu để hỗ trợ nhận diện, không làm nguồn sự thật duy nhất.
- Không dùng Label thay cho Permission hoặc trạng thái quy trình.
- Xóa Label thận trọng vì nó có thể đang gắn với nhiều loại tài nguyên.
