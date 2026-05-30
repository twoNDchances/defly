# Engine

`App\Models\Engine`

Engine là bước biến đổi dữ liệu sau khi target đã trích xuất giá trị. Một target có thể chạy nhiều engine theo thứ tự để chuẩn hóa, chuyển kiểu, tính toán, hash hoặc tách dữ liệu trước khi rule so sánh.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của engine. |
| `name` | `string` | Tên engine. |
| `input_datatype` | `Datatype` | Kiểu dữ liệu đầu vào: `array`, `number`, `string`. |
| `type` | `string` | Loại biến đổi, ví dụ `lower`, `upper`, `trim`, `length`, `hash`, `split`, `merge`, các phép toán số học. |
| `configurations` | `array` | Tham số riêng cho engine, ví dụ thuật toán hash hoặc ký tự split. |
| `output_datatype` | `Datatype` | Kiểu dữ liệu sau khi biến đổi. |
| `description` | `string` | Mô tả mục đích. |
| `created_by` | `string` | User tạo engine. |
| `is_locked` | `boolean` | Khóa chỉnh sửa với engine hệ thống. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `targets()` | many-to-many | Target sử dụng engine qua bảng `targets_engines`, có `order` để xác định pipeline biến đổi. |
| `labels()` | morph many-to-many | Nhãn quản trị engine. |
| `createdBy()` | belongs-to | User tạo engine. |

## Ghi chú vận hành

Thứ tự engine rất quan trọng. Ví dụ `trim` trước `length` sẽ cho kết quả khác với `length` trước `trim`.
