# Permission

Permission là một quyền theo cặp **mô hình dữ liệu + hành động**. Permission có thể gắn trực tiếp vào [User](User.md), [Key](Key.md), hoặc gián tiếp qua [Group](Group.md).

## Các trường cấu hình

| Trường | Bắt buộc | Ràng buộc |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, tối đa 255 ký tự. Không bắt buộc kebab-case. |
| `applied_for` | Có | Tên mô hình dữ liệu có lớp chính sách trong Manager, ví dụ `Rule`, `Principle`, `Defender`. |
| `action` | Có | Hành động được lớp chính sách của mô hình dữ liệu hỗ trợ. |
| `description` | Không | Mô tả phạm vi quyền. |

Khi đổi `applied_for`, Manager xóa hành động đang chọn và nạp lại danh sách hành động hợp lệ từ lớp chính sách. Bộ kiểm tra không chấp nhận cặp mô hình dữ liệu/hành động không tồn tại.

## Danh mục hành động

Tùy lớp chính sách của mô hình dữ liệu, danh sách có thể gồm:

| Hành động | Ý nghĩa |
| --- | --- |
| `all` | Toàn quyền trên mô hình dữ liệu. |
| `viewAny`, `view` | Liệt kê và xem một bản ghi. |
| `create`, `update` | Tạo và cập nhật. |
| `deleteAny`, `delete` | Xóa hàng loạt và xóa một bản ghi. |
| `clone` | Nhân bản bản ghi. |
| `validate`, `validateAny` | Kiểm tra Principle một hoặc hàng loạt. |
| `deploy`, `deployAny` | Triển khai Defender. |
| `cancel`, `cancelAny` | Hủy triển khai Defender. |
| `follow` | Theo dõi nhật ký triển khai. |
| `refresh` | Làm mới phản hồi giao tiếp Defender. |
| `apply`, `applyAny` | Áp dụng Principle vào Defender. |
| `revoke`, `revokeAny` | Thu hồi Principle. |
| `implement`, `implementAny` | Kích hoạt Decision. |
| `suspend`, `suspendAny` | Tạm ngưng Decision. |
| `review`, `reviewAny` | Đánh dấu Report đã xem xét. |

Danh sách thực tế được sinh tự động từ các phương thức công khai của lớp chính sách. Không phải mô hình dữ liệu nào cũng có mọi hành động.

Các loại trừ hệ thống:

- `Pattern`: không có `create`, `update`, `deleteAny`, `delete`.
- `Report`: không có `create`, `update`.
- `Timeline`: không có `create`, `update`.

## Cách lớp `Security` đánh giá quyền

Trước hết, User phải tồn tại, có `is_verified = true` và `is_activated = true`. Sau đó hệ thống chọn chủ thể Permission:

1. Nếu yêu cầu API có Key và `key.is_reused = false`, chủ thể là Key.
2. Trong các trường hợp khác, chủ thể là User.

Tiếp theo:

```text
if subject là User và user.is_root:
    cho phép
else if subject có Model:all trực tiếp hoặc qua group:
    cho phép
else if subject có Model:action trực tiếp hoặc qua group:
    cho phép
else:
    từ chối
```

Permission trực tiếp và Permission từ Group được hợp nhất theo OR. Không có quy tắc từ chối hoặc thứ tự ưu tiên.

Với thao tác Defender có Guard, kết quả Permission chỉ là cổng đầu tiên. [Guard](Guard.md) tiếp tục kiểm tra User hiện tại/người yêu cầu có được thao tác trên Defender cụ thể đó không.

## Permission không phải điều kiện duy nhất

Có Permission chưa chắc hành động sẽ chạy. Phần xác thực quyền còn kiểm tra trạng thái bản ghi:

- Bản ghi `is_locked = true` bị chặn với `update`, `delete`, `validate`.
- Defender đang `pending`/`processing` bị chặn cập nhật/xóa/triển khai.
- Defender đã triển khai thành công không được xóa trước khi hủy.
- Defender gắn với một hoặc nhiều [Guard](Guard.md) yêu cầu User hiện tại/người yêu cầu là chủ sở hữu Defender hoặc thuộc một Guard khớp và chưa hết hạn.
- Principle đang `pending`/`validating` bị chặn cập nhật/xóa/kiểm tra.
- API Report còn kiểm tra Report có thuộc Defender trong URL hay không.

Permission trả lời “chủ thể có loại quyền này không”; lớp chính sách và quy tắc nghiệp vụ trả lời “quyền đó có dùng được trên bản ghi hiện tại không”.

## Quan hệ

Permission có quan hệ nhiều-nhiều với User, Key và Group, đồng thời có thể gắn [Label](Label.md). `created_by` ghi User tạo Permission.

## Ví dụ

Permission:

```text
name: deploy-defender
applied_for: Defender
action: deploy
```

Permission này cho phép gọi thao tác triển khai trên Defender đủ điều kiện, nhưng không tự cấp `view`, `cancel` hoặc `deployAny`.

Permission `Defender:all` bao phủ các hành động của Defender, nhưng vẫn không bỏ qua ràng buộc trạng thái triển khai hoặc tư cách thành viên Guard trừ khi User cũng là chủ sở hữu Defender đó.

## Danh sách kiểm tra

- Đặt tên mô tả đúng mô hình dữ liệu và hành động.
- Chỉ dùng `all` khi chủ thể thực sự cần toàn quyền mô hình dữ liệu.
- Kiểm tra cả Permission trực tiếp và Group khi điều tra quyền.
- Với API, xác định chủ thể đang là Key hay User theo `is_reused`.
- Không nhầm Permission với quyền sở hữu, tư cách thành viên Guard, trạng thái khóa hoặc trạng thái quy trình.
