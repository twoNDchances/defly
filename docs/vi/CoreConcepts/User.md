# User

`App\Models\User`

User là tài khoản quản trị trong manager. User có thể đăng nhập Filament, sở hữu tài nguyên cấu hình, thuộc group và có permission trực tiếp.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của user. |
| `name` | `string` | Tên hiển thị. |
| `email` | `string` | Email đăng nhập. |
| `email_verified_at` | `datetime` | Thời điểm xác thực email. |
| `password` | `hashed` | Mật khẩu đã hash, bị ẩn khi serialize. |
| `is_verified` | `boolean` | Tài khoản đã xác minh. |
| `is_root` | `boolean` | Tài khoản root. |
| `is_activated` | `boolean` | Tài khoản đang được kích hoạt. |
| `verification_token` | `string` | Token xác minh, bị ẩn khi serialize. |
| `created_by` | `string` | User tạo tài khoản này. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ sở hữu tài nguyên

User có các quan hệ `hasMany` tới tài nguyên do mình tạo: `getUsers()`, `getGroups()`, `getPermissions()`, `getLabels()`, `getWordlists()`, `getEngines()`, `getTargets()`, `getActions()`, `getRules()`, `getPrinciples()`, `getDecisions()`, `getDefenders()`, `getKeys()`, `getTimelines()`.

## Quan hệ phân quyền

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `groups()` | many-to-many | Group của user qua `users_groups`. |
| `permissions()` | many-to-many | Permission gắn trực tiếp qua `users_permissions`. |
| `labels()` | morph many-to-many | Nhãn quản trị user. |

## Scope và truy cập

| Thành phần | Ý nghĩa |
| --- | --- |
| `canAccessPanel()` | Chỉ cho vào panel `defly-manager` khi user đã verified và activated. |
| `excludeCurrent` | Loại user hiện tại khỏi query. |
| `excludeRoot` | Ẩn root với user không phải root. |

## Ghi chú vận hành

User là chủ sở hữu mặc định của nhiều cấu hình firewall. Khi lọc dữ liệu theo owner, hệ thống dựa vào `created_by`.
