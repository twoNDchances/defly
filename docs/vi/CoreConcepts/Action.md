# Action

`App\Models\Action`

Action là hành động được thực thi khi một `Rule` khớp. Action thường được gắn vào rule theo thứ tự, sau đó firewall chạy lần lượt để tạo tác động như chặn, cho phép, ghi log, tạo report, thay đổi score hoặc đánh dấu nghi vấn.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của action. |
| `name` | `string` | Tên hiển thị và định danh nghiệp vụ. |
| `type` | `Action\Type` | Loại hành động: `allow`, `deny`, `log`, `request`, `report`, `suspect`, `setter`, `score`, `level`. |
| `configurations` | `array` | Cấu hình riêng cho từng loại action. |
| `description` | `string` | Mô tả mục đích sử dụng. |
| `created_by` | `string` | User tạo action. |
| `is_locked` | `boolean` | Đánh dấu action hệ thống hoặc action không nên chỉnh sửa. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `rules()` | many-to-many | Các rule sử dụng action qua bảng `rules_actions`, có `order` để quyết định thứ tự chạy. |
| `reports()` | one-to-many | Các report được trigger bởi action này qua cột `triggered_by`. |
| `labels()` | morph many-to-many | Nhãn gắn cho action thông qua trait `Labellable`. |
| `createdBy()` | belongs-to | User tạo action thông qua trait `Owner` và cột `created_by`. |

## Ghi chú vận hành

`configurations` phải được diễn giải theo `type`. Ví dụ action `log` cần thông tin nơi ghi log, action `report` cần cấu hình tạo report, còn action `score` hoặc `level` sẽ tác động vào trạng thái runtime của transaction.
