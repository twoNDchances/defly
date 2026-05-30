# Decision

`App\Models\Decision`

Decision là quyết định cuối cùng được áp dụng sau khi firewall đã tính trạng thái của request hoặc response. Decision nhìn vào direction, condition và score để chọn action ở tầng xử lý request hoặc response.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của decision. |
| `name` | `string` | Tên decision. |
| `direction` | `Decision\Direction` | Hướng áp dụng: `request` hoặc `response`. |
| `condition` | `Decision\Condition` | Điều kiện so sánh score: `<=`, `<`, `=`, `>=`, `>`. |
| `score` | `float` | Ngưỡng điểm dùng trong điều kiện. |
| `action` | `Decision\Action` | Hành động quyết định như `allow`, `deny`, `redirect`, `rewrite`, `cancel`, `save`, `erase_cookies`, `force_no_cache`. |
| `configurations` | `array` | Cấu hình cho action của decision. |
| `description` | `string` | Mô tả mục đích. |
| `created_by` | `string` | User tạo decision. |
| `is_locked` | `boolean` | Khóa chỉnh sửa khi decision thuộc bộ mặc định hoặc hệ thống. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `defenders()` | many-to-many | Defender đang dùng decision qua bảng `defenders_decisions`, có `order` và `is_implemented`. |
| `labels()` | morph many-to-many | Nhãn quản trị decision. |
| `createdBy()` | belongs-to | User tạo decision thông qua `created_by`. |

## Ghi chú vận hành

Decision không tự phát hiện tấn công. Nó chỉ đọc score và trạng thái đã được rule/action tạo ra trước đó, rồi quyết định bước cuối như tiếp tục proxy, chặn, rewrite hoặc lưu request.
