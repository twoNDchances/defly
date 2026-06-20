# Report

Report là ảnh chụp sự kiện tường lửa do [Action](Action.md#report) `report` tạo. Nó lưu yêu cầu, phản hồi và dấu vết của Rule đã kích hoạt để điều tra vì sao lưu lượng bị đánh dấu.

Report là dữ liệu trong quá trình chạy, không được tạo hoặc cập nhật thủ công qua chính sách/giao diện. Người có quyền chỉ xem, đánh dấu đã xem xét hoặc xóa.

## Các trường lưu trữ

| Trường | Kiểu | Ý nghĩa |
| --- | --- | --- |
| `metas` | Đối tượng JSON | IP, URL, mã trạng thái HTTP, phương thức và giao thức. |
| `request_headers` | Mảng JSON | Danh sách `{key, value}` của tiêu đề HTTP trong yêu cầu. |
| `request_body` | Đối tượng JSON | Nội dung yêu cầu đã phân tích/phân loại. |
| `response_headers` | Mảng JSON | Danh sách `{key, value}` của tiêu đề HTTP trong phản hồi. |
| `response_body` | Đối tượng JSON | Nội dung phản hồi đã phân tích/phân loại. |
| `rule_details` | Đối tượng JSON | Đầu ra Target, dấu vết Engine, phép so sánh và giá trị khớp. |
| `triggered_by` | UUID có thể rỗng | [Action](Action.md) loại `report` tạo bản ghi. |
| `created_by` | UUID có thể rỗng | [Defender](Defender.md) tạo Report. |
| `is_reviewed` | Đúng/sai | Đã được người vận hành xem xét hay chưa. |

Report có UUID và thời điểm tạo/cập nhật. Khác phần lớn mô hình dữ liệu trong Manager, `created_by` ở đây trỏ Defender, không trỏ User.

## Siêu dữ liệu

Quá trình chạy lưu:

```json
{
  "ip": "203.0.113.10",
  "url": "https://example.com/login?next=/admin",
  "status": 403,
  "method": "POST",
  "protocol": "HTTP/1.1"
}
```

URL ưu tiên URL đầy đủ của giao dịch HTTP. Nếu chưa có, Defender ghép giao thức URL, tên máy chủ và URL nguyên bản; giao thức dự phòng là `http`, đường dẫn rỗng dự phòng là `/`.

## Tiêu đề HTTP

Mỗi khóa tiêu đề HTTP tạo một phần tử; nhiều giá trị của cùng khóa được nối bằng `; `.

```json
[
  { "key": "Content-Type", "value": "application/json" },
  { "key": "X-Forwarded-For", "value": "203.0.113.10; 10.0.0.5" }
]
```

Thứ tự tiêu đề HTTP lấy từ ánh xạ trong quá trình chạy và không nên được xem là ổn định.

## Nội dung yêu cầu

| Loại nội dung | Cấu trúc Report |
| --- | --- |
| Đối tượng JSON | Lưu đối tượng trực tiếp. |
| Mảng/giá trị đơn JSON | `{ "body": <decoded-value> }`. |
| Biểu mẫu URL-encoded | Đối tượng trường; trường nhiều giá trị là mảng. |
| Multipart | `{ "fields": {...}, "files": {...} }`. |
| Loại khác | `{ "body": "<văn bản nguyên bản>" }`. |

Tệp multipart lưu `filename`, `size` và `content` dạng chuỗi. Vì Report có thể chứa dữ liệu nhạy cảm hoặc tệp lớn, cần kiểm soát thời hạn lưu giữ và quyền xem Report.

## Nội dung phản hồi

JSON được xử lý giống yêu cầu. Nội dung không phải JSON được phân loại theo khóa dựa trên loại nội dung:

| Loại nội dung | Khóa |
| --- | --- |
| HTML/XHTML | `html` |
| Văn bản thuần | `text` |
| XML | `xml` |
| Image/audio/video/PDF/zip/octet-stream | `file` |
| Khác | `body` |

Nội dung phản hồi rỗng được lưu thành đối tượng rỗng.

## `rule_details`

Dấu vết gồm các nhóm sau:

### Rule

```json
{
  "id": "<rule-uuid>",
  "name": "detect-login-attack",
  "phase": 3,
  "is_inversed": false
}
```

### Target

Gồm ID, tên, giai đoạn, loại, kiểu dữ liệu; nếu có [Pattern](Pattern.md) hoặc [Wordlist](Wordlist.md), Report lưu thêm siêu dữ liệu của chúng.

### Giá trị và chuỗi Engine

- `target_output`: giá trị bộ trích xuất trả trước Engine.
- `engine_chain`: từng bước gồm ID/tên/loại Engine, kiểu dữ liệu đầu vào/đầu ra, giá trị đầu vào và đầu ra.
- `final_output`: giá trị sau Engine cuối.
- `datatype`: kiểu dữ liệu cuối.

### Phép so sánh và kết quả khớp

- `comparator`: phép so sánh của Rule.
- `expected_values`: giá trị cấu hình hoặc từ Wordlist.
- `matched_values.target`: các giá trị Target khớp.
- `matched_values.expected`: các giá trị đối chiếu khớp.
- `matched_context`: phiên bản rút gọn có `...` để đặt kết quả khớp vào ngữ cảnh.

Nếu danh sách đối chiếu có hơn 10 phần tử, Report không chép toàn bộ; nó giữ dấu `...`, các phần tử khớp và dấu `...` để giới hạn kích thước hiển thị.

## Thời điểm tạo

Action `report` chạy bất đồng bộ. Nó chờ giao dịch HTTP báo dữ liệu Report đã sẵn sàng, thời gian chờ mặc định hai phút, rồi mở kết nối cơ sở dữ liệu với thời gian chờ ghi khoảng ba giây.

Nếu chuỗi kết nối rỗng, quá trình chạy không tạo Report. Lỗi kết nối/ghi được ghi vào nhật ký và không chặn yêu cầu chính.

## Xem xét báo cáo

Action `review` trên Manager đặt `is_reviewed = true` và ghi một [Timeline](Timeline.md) với hành động `review`. Việc xem xét chỉ đi một chiều trong giao diện hiện tại; nút không xuất hiện nếu Report đã được xem xét.

Quyền liên quan:

- `viewAny`, `view`: liệt kê/xem Report.
- `deleteAny`, `delete`: xóa Report.
- `review`, `reviewAny`: đánh dấu đã xem xét.

Report không hỗ trợ quyền `create` hoặc `update`.

## API theo Defender

API Report nằm dưới Defender. Khi xem/xóa một Report, Manager kiểm tra `report.created_by` đúng bằng Defender trong URL; Report của Defender khác trả `404`/không được truy cập qua đường dẫn đó.

## Dữ liệu nhạy cảm

Report có thể chứa tiêu đề HTTP dùng để xác thực, cookie, mật khẩu, token, dữ liệu nhận dạng cá nhân và tệp tải lên. Hiện quá trình chạy ghi dữ liệu khá đầy đủ và không tự che thông tin nhạy cảm.

Khuyến nghị:

- Chỉ cấp quyền Report cho nhóm điều tra cần thiết.
- Đặt chính sách xóa/lưu giữ.
- Hạn chế Action `report` ở Rule thực sự cần dữ liệu điều tra.
- Không đưa Report trực tiếp vào kênh nhật ký hoặc thông báo không được bảo vệ.

## Danh sách kiểm tra khi điều tra

- Xác nhận `triggered_by` và Defender nguồn.
- Đọc `target_output` trước, rồi từng bước Engine và `final_output`.
- So phép so sánh với `expected_values` và `matched_values`.
- Kiểm tra `is_inversed` trước khi kết luận logic Rule.
- Đánh dấu Report đã xem xét sau khi xử lý để tách hàng đợi mới/cũ.
