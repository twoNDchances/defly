# Principle

`App\Models\Principle`

Principle là tập rule có thứ tự trong một phase. Firewall dùng principle để gom rule theo cấp độ và phase, sau đó áp dụng lên defender theo thứ tự cấu hình.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của principle. |
| `name` | `string` | Tên principle. |
| `level` | `integer` | Cấp độ hoặc mức ưu tiên của principle. |
| `phase` | `Phase` | Phase xử lý: 1 đến 6. |
| `validation_status` | `Principle\ValidationStatus` | Trạng thái kiểm tra: `pending`, `validating`, `failed`, `passed`. |
| `validation_details` | `array` | Chi tiết lỗi hoặc kết quả validate. |
| `description` | `string` | Mô tả mục đích. |
| `created_by` | `string` | User tạo principle. |
| `is_locked` | `boolean` | Khóa chỉnh sửa với principle hệ thống. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `rules()` | many-to-many | Rule thuộc principle qua `principles_rules`, có `order` để xác định thứ tự đánh giá. |
| `defenders()` | many-to-many | Defender áp dụng principle qua `defenders_principles`, có `order` và `is_applied`. |
| `labels()` | morph many-to-many | Nhãn quản trị principle. |
| `createdBy()` | belongs-to | User tạo principle. |

## Ghi chú vận hành

Một principle nên gom các rule cùng phase. Nếu trộn sai phase, validation có thể fail hoặc runtime không chạy đúng dữ liệu mong muốn.
