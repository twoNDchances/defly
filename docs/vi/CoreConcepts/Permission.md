# Permission

`App\Models\Permission`

Permission mô tả một quyền thao tác trên một loại tài nguyên. Quyền có thể gắn trực tiếp cho user, key hoặc gián tiếp qua group.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của permission. |
| `name` | `string` | Tên quyền. |
| `description` | `string` | Mô tả quyền. |
| `applied_for` | `string` | Loại tài nguyên hoặc phạm vi áp dụng. |
| `action` | `string` | Hành động được phép, ví dụ xem, tạo, sửa, xóa hoặc thao tác tùy hệ thống. |
| `created_by` | `string` | User tạo permission. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `users()` | many-to-many | User được gán trực tiếp permission qua `users_permissions`. |
| `keys()` | many-to-many | API key được gán trực tiếp permission qua `keys_permissions`. |
| `groups()` | many-to-many | Group có permission qua `groups_permissions`. |
| `labels()` | morph many-to-many | Nhãn quản trị permission. |
| `createdBy()` | belongs-to | User tạo permission. |

## Ghi chú vận hành

Permission là lớp kiểm soát truy cập của manager, không phải rule firewall. Nó quyết định ai được thao tác cấu hình, không quyết định request có bị chặn hay không.
