# Timeline

`App\Models\Timeline`

Timeline là nhật ký thao tác trong manager. Nó ghi lại ai đã làm gì, từ địa chỉ nào, trên đường dẫn nào và với tài nguyên nào.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của timeline entry. |
| `created_by` | `string` | User tạo hành động. |
| `ipv4` | `string` | Địa chỉ IPv4 của request quản trị. |
| `ipv6` | `string` | Địa chỉ IPv6 của request quản trị. |
| `method` | `string` | HTTP method. |
| `path` | `string` | Đường dẫn được gọi. |
| `action` | `string` | Hành động đã thực hiện. |
| `resource_type` | `string` | Loại tài nguyên liên quan. |
| `resource_id` | `string` | ID tài nguyên liên quan. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `resource()` | morph-to | Tài nguyên bị tác động. |
| `createdBy()` | belongs-to | User tạo timeline entry. |

## Ghi chú vận hành

Timeline phục vụ audit thao tác quản trị, khác với `Report` là audit sự kiện firewall/runtime.
