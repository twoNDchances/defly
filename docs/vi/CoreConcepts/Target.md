# Target

Target xác định **Defender phải lấy dữ liệu nào** trong vòng đời HTTP để một [Rule](Rule.md) so sánh. Nó không tự quyết định yêu cầu có hợp lệ hay không; nhiệm vụ của Target chỉ là trích xuất một giá trị, sau đó chuyển giá trị đó qua chuỗi [Engine](Engine.md).

```text
Giao dịch HTTP -> Target -> Chuỗi Engine -> Phép so sánh Rule
```

Target có thể đọc yêu cầu, phản hồi hoặc biến trong quá trình chạy do [Action](Action.md#setter) `setter` tạo trước đó.

## Các trường cấu hình

| Trường | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `phase` | Có | Giai đoạn dữ liệu xuất hiện trong vòng đời HTTP, từ `1` đến `6`. |
| `type` | Có | Nguồn dữ liệu trong giai đoạn: `getter`, `full`, `header`, `meta`, `query`, `body` hoặc `file`. |
| `pattern_id` | Tùy loại | [Pattern](Pattern.md) tích hợp sẵn dùng để lấy dữ liệu tổng hợp. Bắt buộc với `full` và `meta`. |
| `name` | Có | Tên duy nhất của Target; đồng thời là khóa cần đọc khi Target không dùng Pattern. |
| `datatype` | Có | Kiểu dữ liệu đầu ra khai báo: `array`, `number` hoặc `string`. |
| `wordlist_id` | Có điều kiện | Bắt buộc khi kiểu dữ liệu là `array` và không có Pattern. |
| `description` | Không | Ghi chú quản trị. |

`name` dài tối đa 255 ký tự, phải duy nhất và viết thường theo dạng kebab-case, ví dụ `authorization-header` hoặc `request-credentials`.

Khi chọn Pattern, Manager tự đặt `datatype` theo kiểu dữ liệu của Pattern và khóa trường này trên biểu mẫu. Trong quá trình chạy, Defender cũng ưu tiên kiểu dữ liệu của Pattern thay vì kiểu dữ liệu lưu trực tiếp trên Target.

## Sáu giai đoạn HTTP

| Giai đoạn | Tên | Dữ liệu đã sẵn sàng |
| --- | --- | --- |
| `1` | Toàn bộ yêu cầu | Yêu cầu nguyên bản hoàn chỉnh. |
| `2` | Tiêu đề HTTP của yêu cầu | Tiêu đề HTTP, chuỗi truy vấn và siêu dữ liệu yêu cầu. |
| `3` | Nội dung yêu cầu | Nội dung yêu cầu và tệp tải lên. |
| `4` | Tiêu đề HTTP của phản hồi | Tiêu đề HTTP và siêu dữ liệu phản hồi. |
| `5` | Nội dung phản hồi | Nội dung phản hồi. |
| `6` | Toàn bộ phản hồi | Phản hồi nguyên bản hoàn chỉnh. |

Giai đoạn của Target phải khớp giai đoạn của [Rule](Rule.md) và [Principle](Principle.md) sử dụng nó. Nếu Defender được yêu cầu đọc Target ở giai đoạn khác, bộ trích xuất trả về `nil` và Rule thường sẽ không khớp.

## Loại hợp lệ theo giai đoạn

| Giai đoạn | Loại hợp lệ |
| --- | --- |
| `1` | `getter`, `full` |
| `2` | `getter`, `full`, `header`, `meta`, `query` |
| `3` | `getter`, `full`, `body`, `file` |
| `4` | `getter`, `full`, `header`, `meta` |
| `5` | `getter`, `full`, `body` |
| `6` | `getter`, `full` |

Manager chỉ hiển thị các loại tương ứng với giai đoạn. Khi đổi giai đoạn, biểu mẫu đặt lại loại thành `getter` và xóa Pattern đang chọn; khi đổi loại, Pattern cũng được xóa để tránh giữ một tổ hợp không hợp lệ.

## Ý nghĩa từng loại

### `getter`

Đọc biến trong quá trình chạy có khóa trùng với `name` của Target. Biến phải được một Action `setter` tạo trước đó trong **cùng giao dịch HTTP**.

Ví dụ, Action tại giai đoạn `2` đặt biến `authenticated-user = admin`. Một Target ở giai đoạn `3`, loại `getter`, tên `authenticated-user` sẽ đọc được `admin`.

Nếu biến không tồn tại, kết quả là `nil`. Biến trong quá trình chạy không được lưu sang yêu cầu kế tiếp.

### `full`

Đọc biểu diễn đầy đủ của giai đoạn. Loại này bắt buộc chọn Pattern để phân biệt yêu cầu nguyên bản, phản hồi nguyên bản, toàn bộ tiêu đề HTTP hoặc toàn bộ nội dung.

Nếu dữ liệu cũ hoặc dữ liệu nhập ngoài Manager tạo một Target `full` không có Pattern, Defender trả về `nil` thay vì tự suy đoán.

### `header`

Đọc tiêu đề HTTP theo `name`, không phân biệt cách viết hoa/thường nhờ chuẩn hóa tên tiêu đề.

- Một giá trị tiêu đề HTTP trả về chuỗi.
- Nhiều giá trị của cùng tiêu đề HTTP trả về mảng.
- Tiêu đề HTTP không tồn tại trả về `nil`.
- Nếu dùng Pattern, Target có thể lấy toàn bộ khóa, giá trị hoặc số lượng tiêu đề HTTP.

### `meta`

Đọc siêu dữ liệu HTTP không thuộc tiêu đề hoặc nội dung. Manager bắt buộc loại này phải chọn Pattern.

Các Pattern hiện có bao gồm phương thức, giao thức, IP, đường dẫn, giao thức URL, máy chủ, cổng và mã trạng thái phản hồi HTTP. Danh sách đầy đủ nằm tại [Pattern](Pattern.md#danh-mục-pattern-tích-hợp-sẵn).

Bộ trích xuất trong quá trình chạy cũng hiểu các khóa trực tiếp như `method`, `protocol`, `path`, `url`, `host`, `scheme`, `port`, `ip`, `remote_addr`, `content_length`, `status` và `status_code`; tuy nhiên biểu mẫu chuẩn vẫn yêu cầu Pattern cho `meta`.

### `query`

Đọc tham số truy vấn sau dấu `?` trong URL yêu cầu. Nếu không dùng Pattern, `name` là tên tham số cần lấy.

Ví dụ URL `/search?q=defly&page=2` và Target tên `q` trả về `defly`. Khóa truy vấn không tồn tại trả về chuỗi rỗng theo hành vi của `url.Values.Get`.

### `body`

Đọc trường trong nội dung yêu cầu ở giai đoạn `3` hoặc nội dung phản hồi ở giai đoạn `5`.

Defender phân tích các loại nội dung sau:

| Loại nội dung | Cách đọc |
| --- | --- |
| `application/json` | Đọc đối tượng JSON. JSON không phải đối tượng được đặt dưới khóa `body`. |
| `application/x-www-form-urlencoded` | Đọc các trường biểu mẫu; trường có nhiều giá trị trở thành mảng. |
| `multipart/form-data` | Chỉ đọc phần trường biểu mẫu không phải tệp. |
| Loại khác | Toàn bộ nội dung được đặt dưới khóa `body`. |

Target nội dung hỗ trợ đường dẫn phân cách bằng dấu chấm. Ví dụ `profile.email` đọc `email` trong đối tượng `profile`; `items.0.name` đọc phần tử đầu tiên của mảng `items`.

### `file`

Chỉ hợp lệ ở nội dung yêu cầu, giai đoạn `3`, và chỉ đọc các phần có tên tệp trong `multipart/form-data`.

Nếu một trường chứa một tệp, kết quả là nội dung tệp dạng chuỗi. Nếu cùng trường có nhiều tệp, kết quả là mảng nội dung. Các thuộc tính phức tạp như tên tệp, phần mở rộng, phần mở rộng phát hiện từ nội dung, số lượng và tổng kích thước được cung cấp bằng [Pattern](Pattern.md).

## Kiểu dữ liệu

| Kiểu dữ liệu | Ý nghĩa | Phép so sánh sau cùng |
| --- | --- | --- |
| `array` | Danh sách nhiều giá trị. | Nhóm phép so sánh mảng trong [Rule](Rule.md#phép-so-sánh-cho-mảng). |
| `number` | Số được biểu diễn bằng `float64` tại Defender. | Nhóm phép so sánh số. |
| `string` | Một chuỗi đơn. | Nhóm phép so sánh chuỗi. |

Kiểu dữ liệu là giao kèo giữa Target và Engine đầu tiên. Trước khi chạy Engine, Defender chuyển kết quả sang kiểu dữ liệu này. Vì vậy, khai báo sai kiểu có thể biến dữ liệu thành giá trị ngoài mong đợi, ví dụ một chuỗi không phải số được chuyển thành `0` trong Engine số.

## Pattern và Wordlist

Target có ba cách trích xuất chính:

1. **Có Pattern:** Pattern quyết định bộ trích xuất và kiểu dữ liệu; không cần Wordlist.
2. **Không có Pattern, kiểu dữ liệu `array`:** bắt buộc có [Wordlist](Wordlist.md); mỗi dòng là một khóa cần đọc.
3. **Không có Pattern, kiểu dữ liệu `string` hoặc `number`:** `name` của Target đồng thời là khóa đơn cần đọc.

Với Target dạng mảng dựa trên Wordlist, Defender duyệt khóa theo đúng thứ tự trong Wordlist. Mỗi khóa luôn tạo một vị trí trong kết quả; khóa không tồn tại trở thành chuỗi rỗng `""`.

Ví dụ Wordlist:

```text
username
password
otp
```

Nội dung yêu cầu:

```json
{
  "username": "admin",
  "password": "secret"
}
```

Kết quả Target:

```json
["admin", "secret", ""]
```

Ngược lại, Target dùng Pattern `request-body-keys` không đọc các khóa do người dùng liệt kê mà tự trả về khóa thực tế của nội dung:

```json
["username", "password"]
```

## Gắn Engine

Một Target có thể gắn nhiều Engine qua bảng nối `targets_engines`. Bảng nối có trường `order`, và Defender chạy Engine theo thứ tự đó.

Ví dụ:

```text
request header User-Agent
  -> trim
  -> lower
  -> length
  -> number
```

Kiểu dữ liệu đầu ra cuối của chuỗi quyết định phép so sánh nào được phép chọn trong Rule. Xem chi tiết tại [Engine](Engine.md#nối-chuỗi-theo-kiểu-dữ-liệu).

## Khóa do quan hệ

Target có `is_locked`. Manager tự khóa Target khi Target đang được một Rule tham chiếu. Target bị khóa không thể cập nhật hoặc xóa qua luồng được bảo vệ; tháo hết quan hệ sử dụng Target sẽ đồng bộ lại trạng thái khóa.

Wordlist và Engine gắn với Target cũng được khóa khi đang được tham chiếu. Cơ chế này bảo vệ chính sách đã lắp ghép khỏi bị sửa âm thầm.

## Danh sách kiểm tra cấu hình

- Chọn đúng giai đoạn mà dữ liệu đã tồn tại.
- Chỉ chọn loại được phép trong giai đoạn đó.
- Dùng Pattern bắt buộc cho `full` và `meta`.
- Dùng Wordlist khi Target là `array` và không có Pattern.
- Kiểm tra kiểu dữ liệu của Pattern hoặc đầu ra Engine cuối trước khi tạo Rule.
- Kiểm tra lại Principle sau khi thay đổi Target hoặc thứ tự Engine.
