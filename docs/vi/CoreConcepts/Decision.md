# Decision

Decision là phán quyết chạy sau các [Principle](Principle.md). Nó so sánh điểm vi phạm hiện tại với một ngưỡng rồi áp dụng hành động cuối cho hướng `request` hoặc `response`.

Decision không tự phát hiện tấn công. Điểm phải được tạo trước đó bởi [Action](Action.md) `suspect` hoặc `score` trong cùng giao dịch HTTP.

## Luồng thực thi

```text
Principle và Action
        |
        v
Violation score hiện tại
        |
        v
Hướng + Điều kiện + Ngưỡng điểm
        |
        v
Hành động Decision
```

Defender chỉ chạy những Decision đã được gắn và có `is_implemented = true`, theo `order` trong quan hệ `defenders_decisions`.

## Trường chung

| Trường | Bắt buộc | Kiểm tra | Ý nghĩa |
| --- | --- | --- | --- |
| `name` | Có | Duy nhất, tối đa 255 ký tự, chữ thường/số và dấu `-` | Định danh Decision. |
| `direction` | Có | `request` hoặc `response` | Hướng HTTP mà Decision được chạy. |
| `condition` | Có | `<=`, `<`, `=`, `>=`, `>` | Toán tử so sánh điểm. |
| `score` | Có | Numeric, tối thiểu `5`; UI dùng integer và mặc định `5` | Ngưỡng so sánh. |
| `action` | Có | Phải hợp lệ với hướng | Hành động khi điều kiện đúng. |
| `description` | Không | Nullable | Mô tả mục đích và phạm vi. |
| `configurations` | Tự sinh | JSON có thể rỗng | Được Manager dựng từ các trường riêng của hành động. |

Ví dụ:

```text
condition = >=
score = 15
```

Decision khớp khi điểm vi phạm hiện tại lớn hơn hoặc bằng `15`.

## Hướng và thời điểm chạy

### Yêu cầu

Decision `request` chạy sau giai đoạn `1`, `2`, `3` và trước khi yêu cầu được gửi tới máy chủ phía sau. Nó có thể cho phép, chặn, sửa hoặc chuyển yêu cầu.

### Phản hồi

Decision `response` chạy sau giai đoạn `4`, `5`, `6` và trước khi phản hồi được trả cho máy khách. Nó có thể cho phép, chặn hoặc sửa phản hồi.

## Hành động hợp lệ theo hướng

| Hành động | Yêu cầu | Phản hồi | Có cấu hình riêng |
| --- | :---: | :---: | :---: |
| `allow` | Có | Có | Không |
| `deny` | Có | Có | Có |
| `rewrite_headers` | Có | Có | Có |
| `rewrite_body` | Có | Có | Có |
| `redirect` | Có | Không | Có |
| `cancel` | Có | Không | Không |
| `rewrite` | Có | Không | Có |
| `save` | Có | Không | Có |
| `erase_cookies` | Không | Có | Không |
| `force_no_cache` | Không | Có | Không |

Manager từ chối hành động không hợp hướng, ví dụ `redirect` cho phản hồi.

## Cho phép (`allow`)

`allow` không có trường cấu hình. Manager lưu `configurations = null`.

Khi chạy, Defender:

1. Đánh dấu giao dịch được phép đi tiếp.
2. Dừng các Decision còn lại trong hướng hiện tại.
3. Không xóa các thay đổi đã được Decision trước đó tạo.

`allow` ở hướng yêu cầu cho yêu cầu đi tiếp nếu giao dịch HTTP chưa bị chặn/hủy. `allow` ở hướng phản hồi giữ phản hồi hiện tại và dừng các Decision phản hồi phía sau.

## Chặn (`deny`)

### Trường trên Manager và API

| Trường | Bắt buộc | Giá trị |
| --- | --- | --- |
| `deny_directive` | Có | `use_default` hoặc `copy_record` |
| `deny_record` | Khi `copy_record` | UUID của một [Action](Action.md) loại `deny` |

Manager không cho nhập trực tiếp mã trạng thái/loại nội dung/nội dung trong biểu mẫu Decision. Nếu cần phản hồi chặn tùy chỉnh, hãy tạo Action `deny` trước rồi chọn `copy_record`.

### Dùng mặc định

Dữ liệu API gửi vào:

```json
{
  "action": "deny",
  "deny_directive": "use_default"
}
```

JSON được lưu:

```json
{
  "directive": "use_default",
  "record": null,
  "status": null,
  "content_type": null,
  "body": null
}
```

Defender dùng giá trị mặc định hiệu lực:

```text
status: 403
content type: application/json
body: {"message":"request denied"}
```

### Sao chép bản ghi

Dữ liệu API gửi vào:

```json
{
  "action": "deny",
  "deny_directive": "copy_record",
  "deny_record": "<deny-action-uuid>"
}
```

Manager kiểm tra bản ghi tồn tại và có loại `deny`, sau đó sao chép ảnh chụp cấu hình vào Decision:

```json
{
  "directive": "copy_record",
  "record": "<deny-action-uuid>",
  "status": 403,
  "content_type": "json",
  "body": "{\"message\":\"Forbidden\"}"
}
```

Action `deny` nguồn yêu cầu:

- `status`: mã trạng thái HTTP hợp lệ.
- `content_type`: `json` hoặc `html`.
- `body`: chuỗi bắt buộc; JSON phải hợp lệ nếu loại nội dung là JSON.

Cấu hình được sao chép tại thời điểm lưu Decision. Sửa Action `deny` nguồn sau đó không tự cập nhật ảnh chụp; cần mở và lưu lại Decision nếu muốn lấy cấu hình mới.

### Hành vi khi chạy

- Chặn yêu cầu: Defender không gọi máy chủ phía sau và trả phản hồi chặn.
- Chặn phản hồi: Defender thay mã trạng thái, xóa các tiêu đề HTTP cũ của phản hồi, đặt loại nội dung và thay nội dung.
- `deny` dừng các Decision còn lại trong hướng hiện tại.

## Viết lại tiêu đề HTTP

`rewrite_headers` áp dụng lên tiêu đề HTTP của yêu cầu hoặc phản hồi tùy hướng.

### Trường trên Manager và API

| Trường | Bắt buộc | Nội dung |
| --- | --- | --- |
| `rewrite_headers_directive` | Có | `set` hoặc `unset` |
| `rewrite_headers_set` | Khi `set` | Mảng các phần tử `key`, `value` |
| `rewrite_headers_unset` | Khi `unset` | Mảng các phần tử `key` |

Khóa là chuỗi tối đa 255 ký tự. Giao diện chỉ nhận chữ, số, gạch ngang và gạch dưới; giá trị là chuỗi.

Ví dụ set:

```json
{
  "action": "rewrite_headers",
  "rewrite_headers_directive": "set",
  "rewrite_headers_set": [
    {"key": "x-defly-decision", "value": "reviewed"}
  ]
}
```

JSON được lưu:

```json
{
  "directive": "set",
  "execution": [
    {"key": "x-defly-decision", "value": "reviewed"}
  ]
}
```

Ví dụ unset được lưu:

```json
{
  "directive": "unset",
  "execution": [
    {"key": "x-debug-token"}
  ]
}
```

`set` thêm tiêu đề HTTP hoặc thay toàn bộ giá trị hiện có của cùng khóa. `unset` xóa tiêu đề HTTP. Hành động này không tự dừng Decision phía sau.

## Viết lại nội dung

`rewrite_body` được Manager thiết kế để đặt hoặc xóa trường trong nội dung của hướng hiện tại.

### Trường trên Manager và API

| Trường | Bắt buộc | Nội dung |
| --- | --- | --- |
| `rewrite_body_directive` | Có | `set` hoặc `unset` |
| `rewrite_body_set` | Khi `set` | Mảng `key`, `value` |
| `rewrite_body_unset` | Khi `unset` | Mảng `key` |

Ví dụ dữ liệu đầu vào:

```json
{
  "action": "rewrite_body",
  "rewrite_body_directive": "set",
  "rewrite_body_set": [
    {"key": "security.status", "value": "blocked"}
  ]
}
```

JSON được lưu:

```json
{
  "directive": "set",
  "execution": [
    {"key": "security.status", "value": "blocked"}
  ]
}
```

### Giới hạn hiện tại khi chạy

Manager đã lưu cấu trúc `directive + execution`, nhưng Defender hiện chỉ viết lại nội dung khi `configurations` có chuỗi `body` hoặc `value`. Quá trình chạy chưa diễn giải danh sách khóa/giá trị do biểu mẫu Manager tạo.

Vì vậy Decision `rewrite_body` tạo từ Manager hiện chưa đặt/xóa trường như giao diện mô tả. Không nên dùng hành động này trong chính sách trên môi trường thật trước khi Defender được đồng bộ và có kiểm thử đầu cuối cho JSON, biểu mẫu và nội dung phản hồi.

## Chuyển hướng

`redirect` chỉ hợp lệ với yêu cầu.

### Trường trên Manager và API

| Trường | Bắt buộc | Kiểm tra |
| --- | --- | --- |
| `redirect_url` | Có | URL hợp lệ |

JSON được lưu:

```json
{
  "url": "https://alternative-backend.example/internal"
}
```

Đây không phải chuyển hướng HTTP `3xx` trả về máy khách. Defender thay URL và Host của yêu cầu để chuyển yêu cầu tới máy chủ phía sau khác, sau đó dừng toàn bộ Decision yêu cầu/phản hồi còn lại của giao dịch HTTP.

## Hủy kết nối (`cancel`)

`cancel` chỉ hợp lệ với yêu cầu và không có cấu hình. Manager lưu `configurations = null`.

Defender đánh dấu hủy, dừng toàn bộ Decision và cố giành quyền điều khiển kết nối để đóng kết nối máy khách. Không tạo phản hồi chặn. Hành vi thực tế còn phụ thuộc máy chủ/giao thức có hỗ trợ việc giành quyền kết nối hay không.

## Viết lại yêu cầu

`rewrite` chỉ hợp lệ với yêu cầu và có hai loại: `path` hoặc `query`.

### Viết lại đường dẫn

| Trường | Bắt buộc | Kiểm tra |
| --- | --- | --- |
| `rewrite_type` | Có | `path` |
| `rewrite_path` | Có | Chuỗi bắt đầu bằng `/` |

Dữ liệu đầu vào:

```json
{
  "action": "rewrite",
  "rewrite_type": "path",
  "rewrite_path": "/safe-path"
}
```

JSON được lưu:

```json
{
  "type": "path",
  "path": "/safe-path",
  "query": null
}
```

Defender thay `request.URL.Path`. Phần truy vấn hiện có được giữ nguyên.

### Đặt tham số truy vấn

| Trường | Bắt buộc | Giá trị |
| --- | --- | --- |
| `rewrite_type` | Có | `query` |
| `rewrite_query_directive` | Có | `set` |
| `rewrite_query_set` | Có | Mảng `key`, `value` |

JSON được lưu:

```json
{
  "type": "query",
  "path": null,
  "query": {
    "directive": "set",
    "execution": [
      {"key": "reviewed", "value": "1"}
    ]
  }
}
```

Defender dùng thao tác đặt: thêm khóa mới hoặc thay giá trị hiện tại của cùng khóa.

### Xóa tham số truy vấn

```json
{
  "type": "query",
  "path": null,
  "query": {
    "directive": "unset",
    "execution": [
      {"key": "debug"}
    ]
  }
}
```

Defender xóa khóa khỏi truy vấn. Viết lại yêu cầu không tự dừng Decision phía sau.

## Lưu yêu cầu

`save` chỉ hợp lệ với yêu cầu.

### Trường trên Manager và API

| Trường | Bắt buộc | Kiểm tra |
| --- | --- | --- |
| `save_position` | Có | `prefix` hoặc `suffix`; mặc định trên giao diện là `prefix` |
| `save_name` | Có | Chuỗi không chứa `/`, `\\`, `:`, `*`, `?`, `"`, `<`, `>` hoặc `|` |

JSON được lưu:

```json
{
  "position": "prefix",
  "name": "blocked-request"
}
```

Defender lưu yêu cầu nguyên bản vào `storage/requests`. Manager không có trường đổi thư mục.

Tên tệp:

```text
prefix: <name>-<dấu thời gian UTC>.http
suffix: <dấu thời gian UTC>-<name>.http
```

Nếu nhập `request.json`, tệp vẫn được thêm đuôi `.http`, ví dụ `request.json-20260619-103000.000000000.http`.

Tệp được tạo với quyền `0600`. Lỗi tạo thư mục hoặc ghi tệp được ghi vào nhật ký; Decision phía sau vẫn tiếp tục.

## Xóa cookie (`erase_cookies`)

`erase_cookies` chỉ hợp lệ với phản hồi và không có cấu hình.

Defender:

1. Xóa các tiêu đề HTTP `Set-Cookie` mà máy chủ phía sau đã tạo trong phản hồi hiện tại.
2. Đọc cookie có trong yêu cầu.
3. Thêm `Set-Cookie` hết hạn, `Path=/`, `Max-Age=0` cho từng tên cookie.

Hành động này không tự dừng Decision phía sau. Cookie có Domain/Path đặc thù có thể cần logic xóa bổ sung vì quá trình chạy hiện đặt đường dẫn `/` và không đặt miền.

## Buộc không dùng bộ nhớ đệm

`force_no_cache` chỉ hợp lệ với phản hồi và không có cấu hình.

Defender đặt:

```http
Cache-Control: no-store, no-cache, must-revalidate, max-age=0
Pragma: no-cache
Expires: 0
```

Hành động không tự dừng Decision phía sau.

## Thứ tự và cơ chế dừng

| Hành động | Dừng hướng hiện tại | Dừng cả Decision yêu cầu/phản hồi |
| --- | :---: | :---: |
| `allow` | Có | Không |
| `deny` | Có | Không |
| `redirect` | Có | Có |
| `cancel` | Có | Có |
| Các hành động còn lại | Không | Không |

Sắp Decision cụ thể trước Decision tổng quát. Một `allow` đặt quá sớm có thể làm Decision `deny` phía sau không bao giờ chạy.

## Gắn và cài đặt trên Defender

Gắn Decision vào [Defender](Defender.md) chỉ tạo quan hệ với:

```text
order
is_implemented = false
```

Để Defender dùng Decision trong quá trình chạy:

1. Defender phải có trạng thái triển khai `successful`.
2. Decision phải được gắn vào Defender.
3. Chạy thao tác implement.
4. Worker gọi API điều khiển Defender.
5. Khi phản hồi thành công, Manager cập nhật `is_implemented = true`.

Tạm ngưng thực hiện quy trình ngược lại và đặt `is_implemented = false`. Khi khởi động, Defender chỉ tải Decision đã được cài và sắp xếp theo thứ tự trong bảng nối.

## Ví dụ Decision hoàn chỉnh

Decision chặn yêu cầu khi điểm từ `15` trở lên, dùng cấu hình `deny` đã lưu trong Action:

```json
{
  "name": "deny-high-risk-request",
  "direction": "request",
  "condition": ">=",
  "score": 15,
  "action": "deny",
  "deny_directive": "copy_record",
  "deny_record": "<deny-action-uuid>",
  "description": "Block requests whose accumulated score is high risk."
}
```

Decision thêm tiêu đề HTTP vào phản hồi khi điểm từ `5` trở lên:

```json
{
  "name": "mark-suspicious-response",
  "direction": "response",
  "condition": ">=",
  "score": 5,
  "action": "rewrite_headers",
  "rewrite_headers_directive": "set",
  "rewrite_headers_set": [
    {"key": "x-defly-risk", "value": "suspicious"}
  ]
}
```

## Danh sách kiểm tra

- Hướng đúng với hành động.
- Điểm tối thiểu `5` và điều kiện đúng ý nghĩa.
- Cấu hình chặn tùy chỉnh tham chiếu Action `deny` hợp lệ.
- Danh sách đặt có cả khóa/giá trị; danh sách xóa có khóa.
- Rewrite path bắt đầu bằng `/`.
- URL chuyển hướng là URL máy chủ phía sau hợp lệ, không nhầm với HTTP 3xx.
- Tên cấu hình `save` không chứa ký tự cấm và vùng lưu trữ có quyền ghi.
- Decision đã được gắn, cài và có thứ tự đúng trên Defender.
- Không dùng `rewrite_body` theo khóa trên môi trường thật khi quá trình chạy chưa được đồng bộ.
