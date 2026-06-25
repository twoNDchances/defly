# Action

Action mô tả việc Defender thực hiện khi một [Rule](Rule.md) thuộc [Principle](Principle.md) đã khớp. Action có thể chặn/cho phép giao dịch HTTP, ghi nhật ký, gửi yêu cầu HTTP, tạo [Report](Report.md), thay đổi điểm, cấp độ hoặc biến trong quá trình chạy.

Action khác [Decision](Decision.md): Action chạy bên trong Principle và có thể thay đổi trạng thái giao dịch HTTP; Decision chạy sau các Principle của hướng yêu cầu/phản hồi và dùng tổng điểm để đưa ra phán quyết.

## Trường chung

| Trường | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `type` | Có | Một trong chín loại Action. Mặc định trên biểu mẫu là `allow`. |
| Trường cấu hình theo loại | Tùy loại | Được Manager đóng gói vào JSON `configurations`. |
| `description` | Không | Ghi chú quản trị. |

## Thứ tự và dừng chuỗi

Action được gắn vào Rule qua bảng nối có `order`. Defender duyệt theo thứ tự này.

- Trước mỗi Action, Defender kiểm tra giao dịch HTTP đã được cho phép hoặc bị chặn chưa.
- `allow` và `deny` đặt cờ kết thúc; Action phía sau không chạy.
- Action khác tiếp tục chuỗi.
- `request` và `report` chạy bất đồng bộ bằng goroutine. Defender khởi tạo chúng rồi tiếp tục Action kế tiếp, không chờ kết quả.
- Action không hợp lệ được ghi vào nhật ký kiểm tra; đối tượng đã dựng vẫn có thể được thực thi, tùy lỗi cụ thể.

## Ma trận cấu hình

| Loại | Cấu hình | Đồng bộ | Dừng chuỗi |
| --- | --- | --- | --- |
| `allow` | Không | Có | Có |
| `deny` | `status`, `content_type`, `body` | Có | Có |
| `log` | `format`, `console`, `file` | Có | Không |
| `request` | `url`, `method`, `headers`, `body` | Không | Không |
| `report` | Không | Không | Không |
| `suspect` | `severity` | Có | Không |
| `setter` | `directive`, `execution` | Có | Không |
| `score` | `operator`, `value` | Có | Không |
| `level` | `operator`, `value` | Có | Không |

## `allow`

Không có cấu hình. Action gọi `SetAllow()` trên giao dịch HTTP. Từ thời điểm đó, bộ chạy không thực thi Action, Rule, Principle hoặc Decision phía sau trong nhánh hiện tại.

`allow` nên được đặt ở vị trí mà việc bỏ qua toàn bộ kiểm tra sau là chủ đích rõ ràng.

## `deny`

### Cấu hình Manager

| Trường | Ràng buộc | Mặc định |
| --- | --- | --- |
| `deny_status` | Mã trạng thái HTTP hợp lệ trong danh mục Symfony | `403` |
| `deny_content_type` | `json` hoặc `html` | `json` |
| `deny_body` | Chuỗi bắt buộc | Không có |

JSON lưu:

```json
{
  "status": 403,
  "content_type": "json",
  "body": "{\"message\":\"request denied\"}"
}
```

Defender ánh xạ `json` thành `application/json`, `html` thành `text/html; charset=utf-8`. Với JSON, quá trình chạy kiểm tra nội dung có phải JSON hợp lệ. Nếu cấu hình bị thiếu khi nạp ngoài Manager, giá trị dự phòng là mã `403` và nội dung `{"message":"request denied"}`.

Ở giai đoạn yêu cầu, `deny` ngăn gọi máy chủ phía sau và tạo phản hồi chặn. Ở giai đoạn phản hồi, `deny` thay phản hồi của máy chủ phía sau trước khi gửi cho máy khách.

## `log`

### Cấu hình Manager

| Trường | Ràng buộc | Mặc định trên biểu mẫu |
| --- | --- | --- |
| `log_format` | Chuỗi bắt buộc | `[%time%] ...` |
| `log_console` | Giá trị đúng/sai, bắt buộc | `true` |
| `log_file` | Giá trị đúng/sai, bắt buộc | `true` |

JSON lưu:

```json
{
  "format": "[%time%] %ip% | %method% | %path% | score=%score%",
  "console": true,
  "file": true
}
```

### Định dạng bản ghi

| Tag | Giá trị |
| --- | --- |
| `%pid%` | Process ID của Defender. |
| `%time%` | Thời gian theo format `dd/mm/yyyy HH:mm:ss`. |
| `%referer%` | Tiêu đề HTTP `Referer` của yêu cầu. |
| `%ip%` | IP máy khách từ địa chỉ từ xa. |
| `%ips%` | `X-Forwarded-For`, dự phòng về IP máy khách. |
| `%method%` | Phương thức HTTP. |
| `%path%`, `%route%` | Đường dẫn yêu cầu. |
| `%score%` | Điểm vi phạm hiện tại. |
| `%protocol%` | Giao thức yêu cầu. |
| `%host%` | Máy chủ của yêu cầu. |
| `%url%` | URL yêu cầu. |
| `%ua%` | `User-Agent`. |
| `%status%` | Mã trạng thái phản hồi hiện tại. |
| `%resbody%` | Nội dung phản hồi. |
| `%reqheaders%` | Tiêu đề HTTP của yêu cầu ở dạng JSON. |
| `%queryparams%` | Chuỗi truy vấn đã mã hóa. |
| `%body%` | Nội dung yêu cầu. |
| `%bytesSent%` | Độ dài nội dung phản hồi. |
| `%bytesReceived%` | Độ dài nội dung yêu cầu. |
| `%from%` | Chuỗi `WAF`. |
| `%port%` | Port trong remote address. |
| `%reqheader:Name%` | Một tiêu đề HTTP của yêu cầu. |
| `%respheader:Name%` | Một tiêu đề HTTP của phản hồi. |
| `%query:Name%` | Một tham số truy vấn. |
| `%locals:Name%` | Một biến trong quá trình chạy do `setter` tạo. |

Thẻ không nhận diện được được giữ nguyên trong dòng nhật ký. `%%` tạo ký tự `%`.

### Giới hạn hiện tại

Quá trình chạy ưu tiên tệp khi `file = true` và ghi vào `storage/logs/firewall.log`. Khi không có đường dẫn tệp, nó ghi ra màn hình lệnh. Trường `console` hiện được Manager lưu nhưng phần xử lý chưa dùng để quyết định đầu ra; vì vậy hai công tắc chưa hoạt động độc lập đúng như tên gọi.

## `request`

### Cấu hình Manager

| Trường | Ràng buộc |
| --- | --- |
| `request_url` | Chuỗi bắt buộc; bộ kiểm tra hiện chưa bắt buộc đúng chuẩn URL. |
| `request_method` | `get`, `post`, `put`, `patch`, `delete`; mặc định `get`. |
| `request_headers` | Danh sách `{key, value}`, tùy chọn. Khóa tối đa 255 ký tự. |
| `request_body` | Chuỗi bắt buộc, kể cả GET. |

```json
{
  "url": "https://example.com/events",
  "method": "post",
  "headers": [
    { "key": "Content-Type", "value": "application/json" }
  ],
  "body": "{\"event\":\"blocked\"}"
}
```

Action gửi một yêu cầu phụ và không thay yêu cầu chính. Phần gửi yêu cầu có thời gian chờ mặc định 5 giây.

- Với GET, nội dung được phân tích như chuỗi truy vấn rồi cộng vào truy vấn hiện có của URL.
- Với phương thức khác, nội dung được gửi nguyên văn.
- Tiêu đề HTTP trùng khóa được ghi đè bằng giá trị sau cùng.
- Phản hồi phụ được đọc bỏ và đóng; không cập nhật giao dịch HTTP.
- Lỗi tạo/gửi yêu cầu chỉ được ghi vào nhật ký.

## `report`

Không có trường cấu hình trên Manager. Action chạy bất đồng bộ và ghi một báo cáo vào cơ sở dữ liệu nếu Defender có chuỗi kết nối cơ sở dữ liệu.

Report gồm siêu dữ liệu, tiêu đề HTTP và nội dung của yêu cầu/phản hồi, Action kích hoạt, Defender tạo báo cáo, cùng dấu vết chi tiết của Rule/Target/Engine/phép so sánh. Trước khi ghi, Action chờ giao dịch HTTP báo dữ liệu Report đã sẵn sàng, tối đa mặc định hai phút.

Xem cấu trúc đầy đủ tại [Report](Report.md).

## `suspect`

`suspect_severity` bắt buộc và nhận một trong:

| Mức độ nghiêm trọng | Điểm mặc định |
| --- | --- |
| `info` | `1` |
| `notice` | `2` |
| `warning` | `3` |
| `error` | `4` |
| `critical` | `5` |
| `alert` | `6` |
| `emergency` | `7` |

Điểm thực tế lấy từ các [biến môi trường](../Environment-Variables.md#proxy-và-điểm-vi-phạm) `PROXY_SEVERITY_*` của Defender. Action cộng điểm mức độ nghiêm trọng vào điểm hiện tại. Nếu bảng ánh xạ trong quá trình chạy không có mức tương ứng, giá trị Go mặc định là `0`.

## `setter`

Setter tạo, cập nhật hoặc xóa biến trong quá trình chạy để Target `getter` đọc ở Rule/giai đoạn sau.

### Directive `set`

`setter_set` là danh sách bắt buộc gồm:

| Trường | Ràng buộc |
| --- | --- |
| `key` | Chuỗi tối đa 255 ký tự; biểu mẫu chỉ nhận chữ, số, gạch ngang và gạch dưới. |
| `datatype` | `string` hoặc `number`. |
| `value` | Chuỗi bắt buộc hoặc số tối thiểu `1`. |

```json
{
  "directive": "set",
  "execution": [
    { "key": "risk-source", "datatype": "string", "value": "login" },
    { "key": "attempt-count", "datatype": "number", "value": 3 }
  ]
}
```

### Directive `unset`

`setter_unset` là danh sách khóa cần xóa. Khi lưu, các phần tử này nằm trong `execution`; phần xử lý chỉ dùng trường `key` khi chỉ thị là `unset`.

Biến chỉ tồn tại trong giao dịch HTTP hiện tại, không phải biến môi trường và không được lưu vào cơ sở dữ liệu.

## `score`

`score_value` là số bắt buộc, tối thiểu `1`.

| Operator | Hành vi |
| --- | --- |
| `override` | Gán điểm bằng `value`. |
| `+` | Cộng `value`. |
| `-` | Trừ `value`. |
| `*` | Nhân với `value`. |
| `/` | Chia cho `value`; chia `0` giữ nguyên điểm. |

JSON lưu dùng khóa `operator` và `value`. Điểm có thể trở thành số âm nếu phép trừ tạo kết quả âm; phần xử lý hiện không ép điểm về `0`.

Điểm sau các Principle được [Decision](Decision.md) sử dụng.

## `level`

`level_value` là số tối thiểu `1` và biểu mẫu chỉ nhận số. Quá trình chạy chuyển kết quả cuối thành số nguyên.

| Operator | Hành vi |
| --- | --- |
| `override` | Đặt cấp độ thành `value`. |
| `increase` | Cộng `value`. |
| `decrease` | Trừ `value`. |

Sau phép tính, cấp độ nhỏ hơn `1` được ép về `1`. Cách bộ chạy phản ứng khi cấp độ tăng/giảm được giải thích tại [Principle](Principle.md#cấp-độ-và-thứ-tự-thực-thi).

## Khóa do quan hệ

Action có `is_locked` và bị khóa khi đang gắn vào Rule. Muốn sửa/xóa Action, cần tháo nó khỏi mọi Rule sử dụng trước.

## Danh sách kiểm tra cấu hình

- Đặt `allow`/`deny` đúng vị trí vì chúng dừng phần còn lại.
- Bảo đảm `deny` có nội dung JSON hợp lệ.
- Không dựa vào kết quả của `request` hoặc `report` cho Action kế tiếp.
- Dùng khóa `setter` trùng chính xác với tên Target `getter`.
- Phân biệt `suspect` cộng điểm theo mức độ nghiêm trọng và `score` sửa trực tiếp tổng điểm.
- Kiểm tra lại Principle sau khi đổi Action hoặc thứ tự Action.
