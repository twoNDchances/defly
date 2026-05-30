# Wordlist

`App\Models\Wordlist`

Wordlist là danh sách từ khóa hoặc giá trị dùng cho target và rule. Danh sách có thể đến từ file hoặc JSON, sau đó runtime firewall dùng để so sánh, kiểm tra hoặc trích xuất.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của wordlist. |
| `name` | `string` | Tên wordlist. |
| `type` | `Wordlist\Type` | Nguồn dữ liệu: `file` hoặc `json`. |
| `word_file` | `string` | Đường dẫn file chứa danh sách từ khóa. |
| `word_json` | `array` | Danh sách từ khóa lưu trực tiếp trong database. |
| `word_count` | `integer` | Số lượng từ khóa. |
| `description` | `string` | Mô tả mục đích sử dụng. |
| `created_by` | `string` | User tạo wordlist. |
| `is_locked` | `boolean` | Khóa chỉnh sửa với wordlist hệ thống. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `targets()` | one-to-many | Target sử dụng wordlist qua `wordlist_id`. |
| `rules()` | one-to-many | Rule sử dụng wordlist qua `wordlist_id`. |
| `labels()` | morph many-to-many | Nhãn quản trị wordlist. |
| `createdBy()` | belongs-to | User tạo wordlist. |

## Ghi chú vận hành

Với `type = file`, runtime cần đọc được file từ storage. Với `type = json`, dữ liệu nằm trong `word_json` và nên đồng bộ với `word_count`.
