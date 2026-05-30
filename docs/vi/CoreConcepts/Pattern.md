# Pattern

`App\Models\Pattern`

Pattern là mẫu trích xuất dữ liệu có sẵn cho target. Nó định nghĩa vị trí dữ liệu trong request hoặc response, phase phù hợp, loại tài nguyên và kiểu dữ liệu đầu ra.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của pattern. |
| `name` | `string` | Tên pattern, ví dụ `request-body-values` hoặc `request-file-detected-extensions`. |
| `phase` | `integer` | Phase mà pattern có dữ liệu. |
| `type` | `Type` | Nhóm dữ liệu: `full`, `header`, `meta`, `query`, `body`, `file`, `getter`. |
| `datatype` | `Datatype` | Kiểu dữ liệu pattern trả về: `array`, `number`, `string`. |
| `description` | `string` | Mô tả pattern cho người cấu hình. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `targets()` | one-to-many | Các target sử dụng pattern này qua `pattern_id`. |

## Ghi chú vận hành

Pattern là danh mục nền tảng. Người dùng thường không tự tạo pattern mới nếu runtime firewall chưa hỗ trợ extractor tương ứng.
