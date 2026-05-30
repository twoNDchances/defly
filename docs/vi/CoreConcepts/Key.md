# Key

`App\Models\Key`

Key là thông tin xác thực dùng cho API hoặc tích hợp tự động. Token được hash và bị ẩn khi serialize để tránh lộ credential trong response hoặc log.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của key. |
| `name` | `string` | Tên key. |
| `token` | `hashed` | Token xác thực, được hash và nằm trong danh sách hidden. |
| `expired_at` | `datetime` | Thời điểm hết hạn. |
| `is_reused` | `boolean` | Cho biết key có được phép tái sử dụng hay không. |
| `description` | `string` | Mô tả mục đích sử dụng. |
| `created_by` | `string` | User tạo key. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `groups()` | many-to-many | Group mà key thuộc về qua bảng `keys_groups`. |
| `permissions()` | many-to-many | Permission gắn trực tiếp cho key qua bảng `keys_permissions`. |
| `createdBy()` | belongs-to | User tạo key. |

## Scope

| Scope | Ý nghĩa |
| --- | --- |
| `onlyOwner` | Chỉ lấy key do user hiện tại tạo ra. |

## Ghi chú vận hành

Khi hiển thị key, không dựa vào `token` từ model serialized vì field này đã được ẩn bằng `#[Hidden(['token'])]`.
