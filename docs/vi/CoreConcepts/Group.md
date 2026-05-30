# Group

`App\Models\Group`

Group gom user, key và permission thành một đơn vị phân quyền. Thay vì gán permission lặp lại cho từng user hoặc từng key, có thể gán qua group để quản trị tập trung.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của group. |
| `name` | `string` | Tên group. |
| `description` | `string` | Mô tả phạm vi quyền hoặc mục đích group. |
| `created_by` | `string` | User tạo group. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `users()` | many-to-many | User thuộc group qua bảng `users_groups`. |
| `keys()` | many-to-many | API key thuộc group qua bảng `keys_groups`. |
| `permissions()` | many-to-many | Permission gắn cho group qua bảng `groups_permissions`. |
| `labels()` | morph many-to-many | Nhãn quản trị group. |
| `createdBy()` | belongs-to | User tạo group. |

## Ghi chú vận hành

Group là tầng phân quyền trung gian. Quyền hiệu lực của một user hoặc key có thể đến trực tiếp từ permission riêng hoặc gián tiếp qua group.
