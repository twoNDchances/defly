# User

User là tài khoản con người dùng để đăng nhập Defly Manager, sở hữu tài nguyên và làm chủ thể phân quyền. User có thể nhận [Permission](Permission.md) trực tiếp hoặc gián tiếp qua [Group](Group.md).

## Các trường cấu hình

| Trường | Bắt buộc | Ràng buộc và ý nghĩa |
| --- | --- | --- |
| `name` | Có | Chuỗi tối đa 255 ký tự. |
| `email` | Có | Email hợp lệ, duy nhất, tối đa 255 ký tự. |
| `password` | Khi tạo | Từ 4 đến 255 ký tự; được băm trước khi lưu. |
| `is_activated` | Có | Cho phép hoặc vô hiệu hóa tài khoản; mặc định `true`. |
| `is_root` | Có điều kiện | Quyền quản trị cao nhất; chỉ tài khoản root hiện tại được nhìn thấy/chỉnh trường này. |
| `is_verified` | Khi tạo | Đánh dấu email đã xác minh; mặc định `true`, chỉ chỉnh khi tạo. |

Khi sửa User, để trống mật khẩu sẽ giữ nguyên giá trị băm hiện tại. Mật khẩu, token ghi nhớ đăng nhập và token xác minh bị ẩn khỏi dữ liệu trả ra.

## Điều kiện đăng nhập Manager

User chỉ được truy cập giao diện `defly-manager` khi đồng thời:

```text
is_verified = true
AND is_activated = true
```

`is_verified` xác nhận quy trình email. `is_activated` là công tắc quản trị. Một User đã được xác minh nhưng bị vô hiệu hóa vẫn không vào giao diện và không được [lớp `Security`](Permission.md#cách-lớp-security-đánh-giá-quyền) cấp quyền.

## Xác minh email

Khi tạo User:

- Nếu `is_verified = false`, Manager tạo UUID `verification_token` và đưa email xác minh vào hàng đợi.
- Nếu `is_verified = true`, Manager gọi `markEmailAsVerified()` và lưu `email_verified_at`.
- Lỗi đưa email vào hàng đợi được ghi vào nhật ký ứng dụng; User vẫn được tạo.

Trường `is_verified` trên biểu mẫu chỉ xuất hiện lúc tạo, không phải công tắc sửa tùy ý sau đó.

## User root

User root bỏ qua kiểm tra quyền khi chủ thể phân quyền là chính User đó. User root vẫn phải có `is_verified` và `is_activated`.

Chỉ User đang là root mới có thể tạo hoặc chỉnh `is_root`. Với User không phải root, danh sách quản trị cũng loại các tài khoản root khỏi truy vấn để tránh lộ hoặc thao tác ngoài thẩm quyền.

Khi API dùng [Key](Key.md) có `is_reused = false`, chủ thể quyền là Key chứ không phải User. Trong trường hợp đó, quyền root của người sở hữu không tự động truyền sang Key.

## Quyền trực tiếp và qua Group

User có hai nguồn quyền:

1. Quan hệ `users_permissions`: quyền gắn trực tiếp.
2. Quan hệ `users_groups` -> `groups_permissions`: quyền từ mọi Group mà User tham gia.

Chỉ cần một nguồn cấp đúng `applied_for + action` là được phép. Quyền `all` trên mô hình dữ liệu bao phủ mọi hành động của mô hình đó. Hệ thống hiện không có quyền từ chối; các nguồn quyền được hợp nhất theo phép OR.

Xem thuật toán đầy đủ tại [Permission](Permission.md#cách-lớp-security-đánh-giá-quyền).

## Quyền sở hữu

Các mô hình dữ liệu quản trị lưu `created_by` trỏ về User tạo chúng. User có quan hệ sở hữu với Group, Permission, Label, Wordlist, Engine, Target, Action, Rule, Principle, Decision, Defender, Key và Timeline.

Quyền sở hữu và quyền thao tác là hai khái niệm khác nhau:

- `created_by` cho biết ai tạo/sở hữu bản ghi và phục vụ truy vết.
- Permission quyết định User có thể thực hiện hành động nào.
- Một số chính sách/truy vấn có thể áp thêm giới hạn bản ghi, khóa hoặc trạng thái ngoài Permission.

## Timeline

Tạo, cập nhật và xóa User qua HTTP được ghi vào [Timeline](Timeline.md) nếu yêu cầu có ngữ cảnh người dùng. Timeline giữ User thực thi, IP, phương thức, đường dẫn, hành động và ID tài nguyên.

## Danh sách kiểm tra quản trị

- Dùng email duy nhất và kiểm tra hàng đợi thư khi tạo User chưa xác minh.
- Vô hiệu hóa bằng `is_activated` thay vì xóa khi cần giữ lịch sử kiểm tra.
- Chỉ cấp root cho tài khoản quản trị tối cao.
- Ưu tiên Group cho bộ quyền dùng chung, Permission trực tiếp cho ngoại lệ nhỏ.
- Kiểm tra khóa API riêng vì Key có thể không tái sử dụng quyền của User.
