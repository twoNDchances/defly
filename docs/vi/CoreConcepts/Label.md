# Label

`App\Models\Label`

Label là nhãn phân loại dùng chung cho nhiều loại tài nguyên. Label giúp lọc, nhóm và nhận diện nhanh các cấu hình trong manager mà không làm thay đổi logic runtime.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của label. |
| `name` | `string` | Tên nhãn. |
| `color` | `string` | Màu hiển thị của nhãn. |
| `description` | `string` | Mô tả ý nghĩa nhãn. |
| `created_by` | `string` | User tạo label. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

Label dùng quan hệ polymorphic qua bảng `labels_resources`.

| Quan hệ | Tài nguyên được gắn nhãn |
| --- | --- |
| `users()` | `User` |
| `permissions()` | `Permission` |
| `groups()` | `Group` |
| `wordlists()` | `Wordlist` |
| `engines()` | `Engine` |
| `targets()` | `Target` |
| `actions()` | `Action` |
| `rules()` | `Rule` |
| `principles()` | `Principle` |
| `decisions()` | `Decision` |
| `defenders()` | `Defender` |

## Ghi chú vận hành

Label là metadata quản trị. Firewall runtime không dùng label để đánh giá request hoặc response.
