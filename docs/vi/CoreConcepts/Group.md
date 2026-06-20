# Group

Group gom [User](User.md) hoặc [Key](Key.md) để cấp cùng một tập [Permission](Permission.md). Group giúp quản trị vai trò như `security-admin`, `policy-editor` hoặc `report-reviewer` mà không phải gắn từng quyền cho từng chủ thể.

## Các trường cấu hình

| Trường | Bắt buộc | Ràng buộc |
| --- | --- | --- |
| `name` | Có | Duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `description` | Không | Ghi chú mục đích hoặc phạm vi nhóm. |

Group có UUID, `created_by`, thời điểm tạo/cập nhật và có thể gắn [Label](Label.md).

## Các quan hệ

| Quan hệ | Bảng nối | Ý nghĩa |
| --- | --- | --- |
| `users` | `users_groups` | User nhận quyền từ Group. |
| `keys` | `keys_groups` | Khóa API nhận quyền từ Group khi Key là chủ thể phân quyền. |
| `permissions` | `groups_permissions` | Tập quyền do Group cấp. |
| `labels` | `labels_resources` | Siêu dữ liệu phục vụ phân loại. |

Group không lồng Group khác; mọi thành viên nằm trực tiếp trong Group.

## Cách quyền được hợp nhất

Một User/Key có thể thuộc nhiều Group. Security cho phép hành động nếu:

- Có Permission trực tiếp phù hợp; hoặc
- Bất kỳ Group nào có Permission phù hợp; hoặc
- Có Permission với hành động `all` cho mô hình dữ liệu tương ứng.

Hệ thống không có thứ tự ưu tiên và không có quyền từ chối. Vì vậy thêm Group chỉ có thể mở rộng quyền, không thể thu hồi quyền đã đến từ nguồn khác.

Ví dụ:

```text
group policy-readers: Principle:viewAny, Principle:view
group policy-editors: Principle:update, Principle:validate
user thuộc cả hai: có hợp của bốn quyền
```

## Group với API Key

Key có `is_reused = false` dùng Permission trực tiếp và Group của **Key**. Group của User sở hữu không được dùng.

Key có `is_reused = true` dùng User làm chủ thể quyền; khi đó Group của **User** được dùng và Group gắn riêng với Key không tham gia quyết định. Xem [Key](Key.md#is_reused-và-chủ-thể-phân-quyền).

## Thiết kế Group

Nên tạo Group theo trách nhiệm ổn định thay vì theo từng người:

- `policy-readers`: chỉ xem chuỗi xử lý của tường lửa.
- `policy-editors`: tạo, sửa và kiểm tra chính sách.
- `defender-operators`: triển khai, hủy, áp dụng, thu hồi, cài đặt và tạm ngưng.
- `report-reviewers`: xem và đánh dấu báo cáo đã xem xét.

Không nên đặt toàn bộ quyền vào một Group nếu các thành viên không cần cùng phạm vi ảnh hưởng.

## Lịch sử thao tác

Tạo, sửa hoặc xóa Group được ghi vào [Timeline](Timeline.md). Việc thay đổi quan hệ thành viên/quyền cần được xem như thay đổi bảo mật, dù bản thân Group chỉ có hai trường dữ liệu.

## Danh sách kiểm tra

- Đặt tên thể hiện vai trò, không thể hiện tên cá nhân.
- Kiểm tra quyền trùng lặp giữa các Group trước khi kết luận đã thu hồi quyền.
- Phân biệt Group của User và Group của Key.
- Dùng Permission `all` rất thận trọng.
