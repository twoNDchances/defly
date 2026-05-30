# Target

`App\Models\Target`

Target định nghĩa dữ liệu mà rule sẽ đọc từ request, response hoặc trạng thái runtime. Target có thể dựa trên pattern có sẵn, wordlist và pipeline engine để biến đổi giá trị trước khi so sánh.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của target. |
| `name` | `string` | Tên target hoặc key cụ thể cần lấy. |
| `phase` | `Phase` | Phase mà target hợp lệ. |
| `type` | `Type` | Nhóm dữ liệu: `full`, `header`, `meta`, `query`, `body`, `file`, `getter`. |
| `datatype` | `Datatype` | Kiểu dữ liệu target trả về: `array`, `number`, `string`. |
| `description` | `string` | Mô tả dữ liệu được trích xuất. |
| `pattern_id` | `string` | Pattern dùng để trích xuất dữ liệu. |
| `wordlist_id` | `string` | Wordlist phụ trợ nếu target cần đọc danh sách từ cấu hình. |
| `created_by` | `string` | User tạo target. |
| `is_locked` | `boolean` | Khóa chỉnh sửa với target hệ thống. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `pattern()` | belongs-to | Pattern xác định cách lấy dữ liệu. |
| `wordlist()` | belongs-to | Wordlist gắn với target nếu có. |
| `engines()` | many-to-many | Engine biến đổi dữ liệu qua `targets_engines`, có `order`. |
| `rules()` | one-to-many | Rule sử dụng target qua `target_id`. |
| `labels()` | morph many-to-many | Nhãn quản trị target. |
| `createdBy()` | belongs-to | User tạo target. |

## Ghi chú vận hành

Target là đầu vào trực tiếp của rule. Với dữ liệu upload, các pattern như `request-file-extensions` lấy extension từ tên file, còn `request-file-detected-extensions` lấy extension từ chữ ký nội dung file.
