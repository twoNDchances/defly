# Pattern

Pattern là bộ trích xuất tích hợp sẵn có tên cố định. Nó giải quyết các giá trị tổng hợp mà người dùng không thể mô tả chỉ bằng `phase`, `type`, `datatype` và tên trường của [Target](Target.md), ví dụ toàn bộ yêu cầu nguyên bản, danh sách khóa trong nội dung, phương thức HTTP hoặc tổng kích thước tệp tải lên.

Pattern không phải biểu thức chính quy. Biểu thức chính quy thuộc phép so sánh của [Rule](Rule.md).

## Các thuộc tính

| Trường | Ý nghĩa |
| --- | --- |
| `name` | Tên mà Defender dùng để chọn hàm trích xuất. Tên phải duy nhất, tối đa 255 ký tự. |
| `phase` | Giai đoạn mà dữ liệu đã sẵn sàng. |
| `type` | Loại nguồn dữ liệu; không được là `getter`. |
| `datatype` | Kiểu dữ liệu Pattern trả về: `array`, `number` hoặc `string`. |
| `description` | Mô tả song ngữ được phần tạo dữ liệu khởi đầu cung cấp. |

Pattern là danh mục hệ thống được tạo bởi `PatternSeeder`. Trong mô hình phân quyền, Pattern không có quyền `create`, `update` hoặc `delete`; người quản trị chọn Pattern có sẵn thay vì tự tạo bộ trích xuất mới trên giao diện.

## Cách Target dùng Pattern

Manager chỉ liệt kê Pattern có `phase` và `type` trùng Target. Khi chọn Pattern:

- Manager tự đặt kiểu dữ liệu Target theo kiểu dữ liệu Pattern.
- Target không cần Wordlist.
- Defender gọi bộ trích xuất theo **tên Pattern**.
- Kiểu dữ liệu Pattern là kiểu dữ liệu đầu vào của chuỗi Engine.

Target loại `full` và `meta` bắt buộc có Pattern. Loại khác có thể dùng Pattern để lấy tập dữ liệu tổng hợp, hoặc không dùng để đọc một khóa do người dùng đặt.

## Danh mục Pattern tích hợp sẵn

### Giai đoạn 1: Toàn bộ yêu cầu

| Pattern | Loại | Kiểu dữ liệu | Kết quả |
| --- | --- | --- | --- |
| `request-full` | `full` | `string` | Yêu cầu nguyên bản gồm dòng yêu cầu, tiêu đề HTTP và nội dung mà giao dịch HTTP lưu giữ. |

### Giai đoạn 2: Tiêu đề HTTP, truy vấn và siêu dữ liệu yêu cầu

| Pattern | Loại | Kiểu dữ liệu | Kết quả |
| --- | --- | --- | --- |
| `request-header-keys` | `header` | `array` | Tên các tiêu đề HTTP của yêu cầu. |
| `request-header-values` | `header` | `array` | Tất cả giá trị tiêu đề HTTP của yêu cầu. |
| `request-header-size` | `header` | `number` | Số khóa tiêu đề HTTP, không phải tổng số giá trị. |
| `request-query-keys` | `query` | `array` | Tên các tham số truy vấn. |
| `request-query-values` | `query` | `array` | Tất cả giá trị tham số truy vấn. |
| `request-query-size` | `query` | `number` | Số khóa truy vấn. |
| `request-meta-url-port` | `meta` | `number` | Cổng của URL yêu cầu. |
| `request-meta-protocol` | `meta` | `string` | Giao thức, ví dụ `HTTP/1.1`. |
| `request-meta-ip` | `meta` | `string` | Địa chỉ từ xa; IPv6 loopback `::1` được chuẩn hóa thành `127.0.0.1`. |
| `request-meta-method` | `meta` | `string` | Phương thức HTTP. |
| `request-meta-url-path` | `meta` | `string` | Đường dẫn URL. |
| `request-meta-url-scheme` | `meta` | `string` | Giao thức URL như `http` hoặc `https`. |
| `request-meta-url-host` | `meta` | `string` | Tên máy chủ của yêu cầu. |
| `request-full-headers` | `full` | `string` | Toàn bộ tiêu đề HTTP của yêu cầu ở dạng `Key: Value\r\n`. |

Thứ tự khóa/giá trị lấy từ map của Go không nên được xem là ổn định. Nếu chính sách phụ thuộc thứ tự, hãy chuẩn hóa bằng cách khác thay vì dựa vào vị trí mảng khóa/giá trị của Pattern.

### Giai đoạn 3: Nội dung và tệp yêu cầu

| Pattern | Loại | Kiểu dữ liệu | Kết quả |
| --- | --- | --- | --- |
| `request-body-keys` | `body` | `array` | Khóa của các trường nội dung không phải tệp. |
| `request-body-values` | `body` | `array` | Giá trị các trường nội dung sau khi đổi thành chuỗi. |
| `request-body-size` | `body` | `number` | Số trường trong nội dung. |
| `request-body-length` | `body` | `number` | Số byte nội dung yêu cầu nguyên bản. |
| `request-full-body` | `full` | `string` | Nội dung yêu cầu nguyên bản. |
| `request-file-keys` | `file` | `array` | Tên trường multipart chứa tệp. |
| `request-file-values` | `file` | `array` | Nội dung từng tệp dạng chuỗi. |
| `request-file-names` | `file` | `array` | Tên tệp do máy khách gửi. |
| `request-file-extensions` | `file` | `array` | Phần mở rộng lấy từ tên tệp, viết thường và không có dấu chấm. |
| `request-file-detected-extensions` | `file` | `array` | Phần mở rộng phát hiện từ nội dung bằng nhận diện MIME. |
| `request-file-size` | `file` | `number` | Tổng số phần tệp, không phải số trường. |
| `request-file-name-size` | `file` | `number` | Số tên tệp, hiện tương đương số phần tệp. |
| `request-file-length` | `file` | `number` | Tổng số byte nội dung tất cả tệp. |

Pattern tệp chỉ có dữ liệu với `multipart/form-data` hợp lệ và đường phân cách đúng. `request-file-detected-extensions` bỏ qua phần tử không phát hiện được phần mở rộng.

### Giai đoạn 4: Tiêu đề HTTP và siêu dữ liệu phản hồi

| Pattern | Loại | Kiểu dữ liệu | Kết quả |
| --- | --- | --- | --- |
| `response-header-keys` | `header` | `array` | Tên các tiêu đề HTTP của phản hồi. |
| `response-header-values` | `header` | `array` | Tất cả giá trị tiêu đề HTTP của phản hồi. |
| `response-header-size` | `header` | `number` | Số khóa tiêu đề HTTP của phản hồi. |
| `response-meta-status` | `meta` | `number` | Mã trạng thái HTTP. |
| `response-meta-protocol` | `meta` | `string` | Giao thức phản hồi. |
| `response-full-headers` | `full` | `string` | Toàn bộ tiêu đề HTTP của phản hồi ở dạng `Key: Value\r\n`. |

### Giai đoạn 5: Nội dung phản hồi

| Pattern | Loại | Kiểu dữ liệu | Kết quả |
| --- | --- | --- | --- |
| `response-body-keys` | `body` | `array` | Khóa của nội dung phản hồi sau khi phân tích. |
| `response-body-values` | `body` | `array` | Giá trị nội dung phản hồi sau khi đổi thành chuỗi. |
| `response-body-size` | `body` | `number` | Số trường trong nội dung phản hồi. |
| `response-body-length` | `body` | `number` | Số byte nội dung phản hồi nguyên bản. |
| `response-full-body` | `full` | `string` | Nội dung phản hồi nguyên bản. |

Nội dung phản hồi dùng cùng quy tắc phân tích JSON, biểu mẫu URL-encoded và giá trị dự phòng `body` được mô tả tại [Target](Target.md#body).

### Giai đoạn 6: Toàn bộ phản hồi

| Pattern | Loại | Kiểu dữ liệu | Kết quả |
| --- | --- | --- | --- |
| `response-full` | `full` | `string` | Phản hồi nguyên bản gồm dòng trạng thái, tiêu đề HTTP và nội dung mà giao dịch HTTP lưu giữ. |

## Pattern không nhận diện

Defender chọn bộ trích xuất bằng `switch` trên tên Pattern. Nếu cơ sở dữ liệu có một Pattern mới nhưng mã nguồn Defender chưa triển khai tên đó, kết quả là `nil`. Vì vậy, thêm Pattern mới đòi hỏi cả migration/dữ liệu khởi đầu phía Manager và phần cài đặt tương ứng phía Defender.

## Kiểm tra hợp lệ

[Principle](Principle.md#kiểm-tra-hợp-lệ) kiểm tra:

- Pattern tồn tại.
- Pattern không được gắn với Target `getter`.
- Giai đoạn và loại của Pattern trùng Target.
- Kiểu dữ liệu Target trùng kiểu dữ liệu Pattern.
- Target `full` và `meta` đã có Pattern.

## Chọn Pattern hay Wordlist

Dùng Pattern khi muốn **một phép trích xuất tích hợp sẵn**, ví dụ lấy tất cả khóa trong nội dung. Dùng [Wordlist](Wordlist.md) khi muốn **tự liệt kê các khóa cụ thể**, ví dụ chỉ lấy `username`, `password` và `otp`.

```text
Pattern request-body-keys -> ["username", "password", "remember"]
Wordlist username/password -> ["admin", "secret"]
```

## Danh sách kiểm tra

- Chọn Pattern đúng giai đoạn và loại.
- Kiểm tra kiểu dữ liệu Pattern trước khi gắn Engine.
- Không hiểu `size` là độ dài byte; danh mục phân biệt rõ `size` và `length`.
- Không dựa vào thứ tự khóa/giá trị của map.
- Khi bổ sung Pattern mới, triển khai bộ trích xuất tương ứng trong Defender.
