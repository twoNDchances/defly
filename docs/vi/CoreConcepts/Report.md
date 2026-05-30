# Report

`App\Models\Report`

Report là bản ghi sự kiện khi firewall phát hiện hoặc xử lý một tình huống cần lưu lại. Report chứa metadata, dữ liệu request/response đã capture, chi tiết rule và action đã trigger.

## Trường dữ liệu

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `id` | `string` | UUID của report. |
| `metas` | `array` | Metadata như IP, method, URL, status hoặc thời điểm xử lý. |
| `request_headers` | `array` | Header request đã capture. |
| `request_body` | `array` | Body request đã parse hoặc snapshot. |
| `response_headers` | `array` | Header response đã capture. |
| `response_body` | `array` | Body response đã parse hoặc snapshot. |
| `rule_details` | `array` | Thông tin rule, target, comparator và giá trị khớp. |
| `triggered_by` | `string` | Action tạo report. |
| `created_by` | `string` | Defender tạo report. |
| `is_reviewed` | `boolean` | Đánh dấu report đã được xem xét. |
| `created_at`, `updated_at` | `datetime` | Thời điểm tạo và cập nhật. |

## Quan hệ

| Quan hệ | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `triggeredBy()` | belongs-to | Action đã trigger report qua `triggered_by`. |
| `createdBy()` | belongs-to | Defender tạo report qua `created_by`. |

## Ghi chú vận hành

Report là dữ liệu điều tra. Không nên dùng report làm nguồn cấu hình runtime vì nội dung phụ thuộc vào request/response thực tế.
