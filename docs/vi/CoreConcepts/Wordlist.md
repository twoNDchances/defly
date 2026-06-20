# Wordlist

Wordlist là danh sách chuỗi có thứ tự, dùng ở hai vị trí:

- [Target](Target.md) có kiểu dữ liệu `array` và không có Pattern: mỗi phần tử là tên trường cần trích xuất.
- [Rule](Rule.md): mỗi phần tử là giá trị hoặc biểu thức chính quy dùng làm giá trị đối chiếu.

## Các trường cấu hình

| Trường | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `type` | Có | `file` hoặc `json`; mặc định trên biểu mẫu là `file`. |
| `word_file` | Khi loại là `file` | Tệp văn bản được tải lên thư mục `wordlists`. |
| `word_json` | Khi loại là `json` | Danh sách đối tượng `{ "word": "..." }`, tối thiểu một phần tử. |
| `word_count` | Hệ thống | Số phần tử được Manager tính khi lưu. |
| `description` | Không | Ghi chú quản trị. |

Mỗi `word_json.*.word` là chuỗi bắt buộc, tối đa 255 ký tự. Biểu mẫu tải tệp giới hạn MIME ở `text/plain`.

## Wordlist JSON

Ví dụ dữ liệu lưu:

```json
[
  { "word": "username" },
  { "word": "password" },
  { "word": "otp" }
]
```

Defender duyệt đúng thứ tự mảng và lấy trường `word` của từng phần tử.

## Wordlist dạng tệp

Mỗi dòng tệp là một phần tử. Defender đọc lần lượt từng dòng, bỏ ký tự xuống dòng nhưng giữ nguyên nội dung còn lại.

```text
username
password
otp
```

Defender thử tìm tệp theo thứ tự:

1. Đường dẫn tuyệt đối.
2. Thư mục gốc cấu hình bởi `WORDLIST_ROOT`.
3. Đường dẫn tương đối hiện tại.
4. `storage/app/public/<path>`.
5. `../manager/storage/app/public/<path>`.
6. `../manager/storage/app/private/<path>`.

Nếu tệp không tồn tại hoặc không đọc được, bộ nạp ghi nhật ký và trả danh sách rỗng.

## `word_count`

Khi lưu tệp, Manager đọc nội dung và đếm các dòng không rỗng sau khi loại khoảng trắng hai đầu. Khi lưu JSON, Manager đếm số phần tử mảng. Đổi loại sẽ xóa dữ liệu của loại còn lại; thay tệp sẽ xóa tệp cũ, và xóa Wordlist sẽ xóa tệp liên quan.

Có một khác biệt trong quá trình chạy cần lưu ý: bộ nạp của Defender hiện vẫn đưa dòng rỗng trong tệp vào danh sách, trong khi `word_count` của Manager bỏ qua dòng rỗng. Nên loại bỏ dòng trắng khỏi tệp để số lượng và hành vi nhất quán.

## Dùng với Target

Khi Target là `array` và không có Pattern, Wordlist chứa **khóa cần đọc**, không phải giá trị mong đợi.

```text
username
password
```

Với nội dung yêu cầu `{"username":"admin","password":"secret"}`, Target trả:

```json
["admin", "secret"]
```

Khóa bị thiếu vẫn tạo phần tử chuỗi rỗng để giữ vị trí. Xem chi tiết tại [Target](Target.md#pattern-và-wordlist).

## Dùng với Rule

| Phép so sánh | Ý nghĩa của từng dòng |
| --- | --- |
| `@similar` | Chuỗi cần so bằng với phần tử của mảng Target. |
| `@search` | Biểu thức chính quy áp dụng lên từng phần tử của mảng Target. |
| `@check` | Chuỗi cần so bằng với Target dạng chuỗi. |
| `@checkRegExp` | Biểu thức chính quy áp dụng lên Target dạng chuỗi. |

Phép so sánh biểu thức chính quy dùng cú pháp RE2 của Go. Một dòng biểu thức lỗi chỉ được xem là không khớp; nó không làm dừng toàn bộ Defender.

## Kiểm tra hợp lệ và khóa

[Principle](Principle.md#kiểm-tra-hợp-lệ) kiểm tra Wordlist liên quan:

- Wordlist có tồn tại và đúng loại.
- Tệp có đường dẫn, tồn tại, đọc được và có nội dung phù hợp.
- JSON là danh sách, mỗi phần tử có trường `word` hợp lệ.
- Số lượng dữ liệu có khớp siêu dữ liệu cần thiết hay không.

Wordlist có `is_locked` và bị khóa khi được Target hoặc Rule tham chiếu. Muốn sửa/xóa, cần tháo toàn bộ quan hệ sử dụng trước.

## Danh sách kiểm tra

- Dùng tệp sạch, không có dòng trắng ngoài ý muốn.
- Không thêm dấu phẩy hoặc dấu nháy nếu chúng không phải một phần của từ.
- Phân biệt khóa Wordlist cho Target và giá trị đối chiếu Wordlist cho Rule.
- Với phép so sánh biểu thức chính quy, kiểm tra từng dòng theo RE2.
- Kiểm tra lại Principle sau khi thay nội dung Wordlist.
