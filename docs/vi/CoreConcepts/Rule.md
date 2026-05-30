# Rule

`App\Models\Rule`

Rule là đơn vị đánh giá chính của firewall. Rule lấy dữ liệu từ target, có thể dùng wordlist, so sánh bằng comparator và kích hoạt action khi điều kiện khớp.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của rule. |
| `name` | `string` | Tên rule. |
| `phase` | `Phase` | Phase mà rule chạy. |
| `target_id` | `string` | Target cung cấp dữ liệu đầu vào. |
| `comparator` | `Rule\Comparator` | Phép so sánh như `@contains`, `@match`, `@equal`, `@greaterThan`, `@regExp`, `@check`. |
| `is_inversed` | `boolean` | Đảo kết quả so sánh. |
| `configurations` | `array` | Cấu hình cho comparator. |
| `wordlist_id` | `string` | Wordlist dùng để so sánh nếu comparator cần danh sách. |
| `description` | `string` | Mô tả mục đích rule. |
| `created_by` | `string` | User tạo rule. |
| `is_locked` | `boolean` | Khóa chỉnh sửa với rule hệ thống. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `target()` | belongs-to | Target được rule đọc dữ liệu. |
| `wordlist()` | belongs-to | Wordlist được rule sử dụng. |
| `actions()` | many-to-many | Action chạy khi rule khớp qua `rules_actions`, có `order`. |
| `principles()` | many-to-many | Principle chứa rule qua `principles_rules`, có `order`. |
| `labels()` | morph many-to-many | Nhãn quản trị rule. |
| `createdBy()` | belongs-to | User tạo rule. |

## Ghi chú vận hành

`phase` của rule nên khớp với phase của target/pattern. Nếu rule chạy trước khi dữ liệu tồn tại, target có thể trả về rỗng hoặc `nil`.
