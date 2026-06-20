# Key

Key là token API thuộc một [User](User.md). API của Defly Manager yêu cầu đồng thời Basic Authentication của User và token của một Key do chính User đó sở hữu.

## Các trường cấu hình

| Trường | Bắt buộc | Ràng buộc |
| --- | --- | --- |
| `name` | Có | Duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `token` | Khi tạo | Từ 16 đến 255 ký tự, duy nhất; biểu mẫu sinh ngẫu nhiên 64 ký tự. |
| `expired_at` | Không | Thời điểm hết hạn. `null` nghĩa là không hết hạn. |
| `is_reused` | Có | Chọn dùng quyền User thay vì quyền riêng của Key; mặc định `false`. |
| `description` | Không | Mô tả mục đích hoặc hệ thống sử dụng Key. |

Token được băm trước khi lưu và bị ẩn trong dữ liệu trả ra. Manager không thể hiển thị lại token nguyên bản từ cơ sở dữ liệu. Khi sửa Key, để trống token sẽ giữ nguyên giá trị băm; nhập token mới sẽ thay token cũ.

## Xác thực API

Lớp xử lý trung gian thực hiện theo thứ tự:

1. Từ Basic Auth, tìm User theo email và kiểm tra giá trị băm của mật khẩu.
2. Đọc token từ vị trí cấu hình, mặc định là tiêu đề HTTP `X-Token-Key`.
3. Chỉ xét Key có `created_by` bằng User trong Basic Auth.
4. Chỉ xét Key chưa hết hạn: `expired_at IS NULL` hoặc `expired_at > now()`.
5. Duyệt các Key hợp lệ và dùng `Hash::check` để tìm token khớp.
6. Đặt User vào ngữ cảnh xác thực và Key vào thuộc tính yêu cầu `authenticated_key`.

Yêu cầu `HEAD` bị lớp xử lý trung gian từ chối với `405 Method Not Allowed`.

Ví dụ:

```http
Authorization: Basic <base64(email:password)>
X-Token-Key: <plaintext-api-token>
Accept: application/json
```

Tên/vị trí token có thể được đổi trong cấu hình xác thực API; nếu vị trí là `body`, token được đọc từ trường cùng tên trong nội dung yêu cầu.

## `is_reused` và chủ thể phân quyền

Tên `is_reused` nên được hiểu theo cách source hiện tại:

| Giá trị | Chủ thể Permission | Nguồn quyền được dùng |
| --- | --- | --- |
| `false` | Key | Permission trực tiếp của Key và Group của Key. |
| `true` | User | Permission trực tiếp của User và Group của User. |

Với `false`, Key có thể bị giới hạn chặt hơn người sở hữu. Ngay cả khi người sở hữu là root, Key vẫn không được bỏ qua kiểm tra quyền vì chủ thể là `Key`.

Với `true`, Permission/Group gắn riêng vào Key không tham gia quyết định; API dùng quyền User giống ngữ cảnh User thông thường.

## Quan hệ

Key có thể gắn:

- [Permission](Permission.md) trực tiếp qua `keys_permissions`.
- [Group](Group.md) qua `keys_groups`.

Key không nằm trong hệ thống [Label](Label.md) hiện tại. Truy vấn `onlyOwner` giới hạn Key theo `created_by` của User đang đăng nhập.

## Hết hạn và thu hồi

`expired_at` được kiểm tra khi xác thực mỗi yêu cầu. Để thu hồi Key có thể:

- Đặt `expired_at` về thời điểm hiện tại/quá khứ.
- Đổi token.
- Xóa Key.
- Giữ Key nhưng gỡ Permission/Group nếu `is_reused = false`.

Nếu `is_reused = true`, gỡ quyền riêng của Key không làm giảm quyền API; phải sửa quyền User hoặc chuyển Key về `false`.

## Bảo mật vận hành

- Chỉ token nguyên bản tại lúc tạo/đổi mới có thể được cung cấp cho bên gọi.
- Không ghi token vào Timeline, nhật ký hoặc phần mô tả.
- Dùng Key riêng cho từng hệ thống tích hợp để thu hồi độc lập.
- Đặt thời hạn cho tích hợp tạm thời.
- Giữ `is_reused = false` khi muốn áp dụng nguyên tắc quyền tối thiểu rõ ràng.
- Luân chuyển token định kỳ và ngay khi nghi ngờ lộ lọt.

## Danh sách kiểm tra

- Lưu token nguyên bản ngay lúc tạo trong hệ thống quản lý bí mật phù hợp.
- Gắn Permission/Group đúng chủ thể dựa trên `is_reused`.
- Kiểm tra User trong Basic Auth phải khớp `created_by` của Key.
- Kiểm tra múi giờ của `expired_at` khi điều tra lỗi 401.
