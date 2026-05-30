# Defender

`App\Models\Defender`

Defender đại diện cho một instance bảo vệ hoặc proxy runtime. Model này lưu cấu hình triển khai, trạng thái vận hành và danh sách principle, decision đang được áp dụng cho instance đó.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của defender. |
| `name` | `string` | Tên instance. |
| `proxy_port` | `integer` | Cổng proxy mà defender sử dụng. |
| `environment_variables` | `array` | Biến môi trường cấp cho runtime. |
| `status` | `Defender\Status` | Trạng thái runtime: `normal` hoặc `abnormal`. |
| `details` | `array` | Thông tin chi tiết về trạng thái hiện tại. |
| `deployment_status` | `Defender\DeploymentStatus` | Trạng thái deploy: `pending`, `processing`, `failed`, `successful`. |
| `deployment_details` | `array` | Log hoặc metadata của quá trình deploy. |
| `last_response_details` | `array` | Thông tin response cuối cùng từ defender. |
| `description` | `string` | Mô tả instance. |
| `created_by` | `string` | User tạo defender. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `principles()` | many-to-many | Các principle được áp dụng qua `defenders_principles`, có `order` và `is_applied`. |
| `decisions()` | many-to-many | Các decision được cài qua `defenders_decisions`, có `order` và `is_implemented`. |
| `reports()` | one-to-many | Report do defender tạo ra qua cột `created_by`. |
| `labels()` | morph many-to-many | Nhãn quản trị defender. |
| `createdBy()` | belongs-to | User tạo defender. |

## Ghi chú vận hành

`deployment_status` phản ánh quá trình đưa cấu hình xuống runtime, còn `status` phản ánh tình trạng hoạt động sau khi defender chạy.
