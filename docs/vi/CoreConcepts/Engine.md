# Engine

Engine là một bước biến đổi dữ liệu của [Target](Target.md) trước khi [Rule](Rule.md) so sánh. Engine dùng để chuẩn hóa chữ, đổi kiểu dữ liệu, tính toán, băm, tách chuỗi hoặc hợp nhất mảng.

```text
Đầu ra Target -> Engine 1 -> Engine 2 -> ... -> Phép so sánh Rule
```

## Các trường cấu hình

| Trường | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `input_datatype` | Có | Kiểu dữ liệu Engine nhận: `array`, `number` hoặc `string`. |
| `type` | Có | Hàm biến đổi; danh sách loại phụ thuộc kiểu dữ liệu đầu vào. |
| Trường tham số | Tùy loại | `position`, `digit`, `hash_method` hoặc `separator`. |
| `output_datatype` | Có, chỉ đọc | Manager tự suy ra từ loại và không cho sửa trực tiếp. |
| `description` | Không | Ghi chú quản trị. |

Khi đổi `input_datatype`, Manager xóa `type` và `output_datatype` đã chọn. Khi chọn loại, kiểu dữ liệu đầu ra được cập nhật tự động.

## Nối chuỗi theo kiểu dữ liệu

Mỗi Engine khai báo `input_datatype` và `output_datatype`. Đầu ra của bước trước phải trùng đầu vào của bước sau.

```text
target string -> trim(string) -> lower(string) -> length(number)
```

Chuỗi trên hợp lệ. `length` đổi kết quả từ `string` sang `number`.

```text
target string -> length(number) -> lower(string input)
```

Ở chuỗi thứ hai, Defender chạy đến `length`, thấy Engine kế tiếp yêu cầu `string` trong khi kiểu dữ liệu hiện tại là `number`, rồi **dừng toàn bộ chuỗi tại đó**. Defender không bỏ qua Engine sai kiểu để thử các Engine phía sau.

Kiểu dữ liệu khởi đầu lấy từ Target; nếu Target dùng [Pattern](Pattern.md), kiểu dữ liệu của Pattern được ưu tiên.

## Engine cho mảng

| Loại | Tham số | Đầu ra | Hành vi |
| --- | --- | --- | --- |
| `indexOf` | `position` là số nguyên, bắt buộc | `string` | Lấy phần tử tại vị trí được chỉ định. |
| `merge` | `separator` tùy chọn | `string` | Ghép các phần tử thành một chuỗi. |

`position` là vị trí bắt đầu từ `0`. Vị trí âm hoặc vượt phạm vi trả về `nil`; khi bước sau chuyển sang chuỗi, giá trị này trở thành chuỗi rỗng.

`merge` chuyển từng phần tử thành chuỗi trước khi ghép. Nếu bỏ trống dấu phân cách, Manager lưu `configurations = null` và Defender dùng dấu phẩy `,`.

Ví dụ:

```text
["admin", "secret"] -> merge("|") -> "admin|secret"
```

## Engine cho số

| Loại | Tham số | Công thức | Đầu ra |
| --- | --- | --- | --- |
| `addition` | `digit` | `value + digit` | `number` |
| `subtraction` | `digit` | `value - digit` | `number` |
| `multiplication` | `digit` | `value * digit` | `number` |
| `division` | `digit` | `value / digit` | `number` |
| `powerOf` | `digit` | `value ^ digit` | `number` |
| `remainder` | `digit` | `value mod digit` | `number` |
| `toString` | Không | Chuyển số thành chuỗi | `string` |

`digit` chấp nhận số nguyên hoặc số thập phân. Chia hoặc lấy phần dư cho `0` trả về `nil`. Các phép toán dùng `float64`.

Nếu giá trị đầu vào không thể phân tích thành số, quá trình chuyển kiểu hiện tại cho ra `0`; cần kiểm soát kiểu dữ liệu Target trước khi dùng phép toán.

## Engine cho chuỗi

| Loại | Tham số | Đầu ra | Hành vi |
| --- | --- | --- | --- |
| `lower` | Không | `string` | Chuyển thành chữ thường. |
| `upper` | Không | `string` | Chuyển thành chữ hoa. |
| `capitalize` | Không | `string` | Viết hoa ký tự đầu tiên. |
| `trim` | Không | `string` | Xóa khoảng trắng hai đầu. |
| `trimLeft` | Không | `string` | Xóa khoảng trắng bên trái. |
| `trimRight` | Không | `string` | Xóa khoảng trắng bên phải. |
| `removeWhitespace` | Không | `string` | Xóa toàn bộ khoảng trắng được nhận diện. |
| `length` | Không | `number` | Trả về số byte của chuỗi. |
| `hash` | `hash_method` | `string` | Băm chuỗi và trả về mã hex viết thường. |
| `split` | `separator` tùy chọn | `array` | Tách chuỗi thành mảng. |

`removeWhitespace` tách chuỗi theo khoảng trắng rồi nối lại không có dấu phân cách. `length` dùng độ dài byte của Go, vì vậy chuỗi Unicode có thể có kết quả lớn hơn số ký tự người dùng nhìn thấy.

`split` dùng dấu phẩy `,` khi bỏ trống dấu phân cách. Nếu dấu phân cách không xuất hiện, kết quả là mảng một phần tử chứa nguyên chuỗi.

## Phương thức băm

`hash` hỗ trợ:

- `md5`
- `sha1`
- `sha224`
- `sha256`
- `sha512`

Nếu cấu hình trong quá trình chạy bị thiếu hoặc không nhận diện được phương thức, Defender mặc định dùng `sha256`. MD5 và SHA-1 chỉ nên dùng để chuẩn hóa/đối chiếu dữ liệu tương thích, không nên dùng để lưu mật khẩu hay cho mục đích mật mã mới.

## Dữ liệu lưu trong `configurations`

Manager chuyển các trường biểu mẫu thành JSON như sau:

```json
{ "position": 0 }
```

```json
{ "digit": 10.5 }
```

```json
{ "hash_method": "sha256" }
```

```json
{ "separator": "|" }
```

Các Engine không cần tham số, hoặc `merge`/`split` bỏ trống dấu phân cách, lưu `configurations` là `null`.

## Thứ tự gắn vào Target

Engine được gắn với Target qua bảng nối có trường `order`. Cùng một tập Engine nhưng thứ tự khác nhau có thể tạo đầu ra và kiểu dữ liệu khác nhau.

Ví dụ:

```text
" A,B " -> trim -> split(",") = ["A", "B"]
" A,B " -> split(",") -> ... = [" A", "B "]
```

[Principle](Principle.md#kiểm-tra-hợp-lệ) kiểm tra chuỗi kiểu dữ liệu đầu vào/đầu ra. Sau khi đổi thứ tự Engine, cần kiểm tra lại Principle sử dụng Target đó.

## Khóa do quan hệ

Engine có `is_locked`. Manager khóa Engine khi nó đang được gắn vào ít nhất một Target. Muốn sửa hoặc xóa Engine, cần tháo nó khỏi toàn bộ Target trước.

## Danh sách kiểm tra cấu hình

- Chọn loại đúng với kiểu dữ liệu đầu vào.
- Xác nhận kiểu dữ liệu đầu ra khớp Engine kế tiếp và phép so sánh cuối.
- Dùng vị trí bắt đầu từ `0` cho `indexOf`.
- Tránh `division` và `remainder` với `digit = 0`.
- Ghi rõ dấu phân cách khi dấu phẩy mặc định không phù hợp.
- Kiểm tra lại Principle sau khi thay đổi Engine hoặc thứ tự chuỗi.
