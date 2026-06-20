# Rule

Rule là một phép so sánh trong tường lửa. Nó lấy đầu ra của [Target](Target.md) sau chuỗi [Engine](Engine.md), dùng phép so sánh đã chọn để đối chiếu với một giá trị cấu hình hoặc [Wordlist](Wordlist.md), rồi trả về `true` hoặc `false` cho [Principle](Principle.md).

```text
Target -> Chuỗi Engine -> Phép so sánh -> true/false
```

## Các trường cấu hình

| Trường | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `phase` | Có | Giai đoạn `1` đến `6` mà Rule được đánh giá. |
| `target_id` | Có | Target cùng giai đoạn cung cấp giá trị so sánh. |
| `comparator` | Có | Phép so sánh phù hợp với kiểu dữ liệu cuối của Target. |
| `is_inversed` | Có | Đảo kết quả logic của Rule; mặc định `false`. |
| Trường giá trị đối chiếu | Tùy phép so sánh | Chuỗi, số, khoảng số hoặc Wordlist dùng để đối chiếu. |
| `description` | Không | Ghi chú quản trị. |

Khi chọn Target, Manager hiển thị kiểu dữ liệu ban đầu và kiểu dữ liệu cuối sau chuỗi Engine. Danh sách phép so sánh được lọc theo kiểu dữ liệu cuối. Khi đổi Target, phép so sánh cũ bị xóa.

Target phải tồn tại và có giai đoạn trùng với Rule. Khi gắn Rule vào Principle, Principle cũng phải cùng giai đoạn.

## Giá trị đối chiếu được lưu

Manager chuyển trường biểu mẫu thành `configurations`:

| Phép so sánh | Trường biểu mẫu | JSON lưu |
| --- | --- | --- |
| `@equal`, `@greaterThan`, `@lessThan`, `@greaterThanOrEqual`, `@lessThanOrEqual` | `number_value` | `{ "number": ... }` |
| `@inRange` | `number_from_value`, `number_to_value` | `{ "number_from": ..., "number_to": ... }` |
| `@contains`, `@match`, `@mirror`, `@startsWith`, `@endsWith`, `@regExp` | `string_value` | `{ "string": "..." }` |
| `@similar`, `@search`, `@check`, `@checkRegExp` | `wordlist_id` | `configurations = null`; giá trị đối chiếu lấy từ Wordlist |

Với `@inRange`, giá trị `from` phải nhỏ hơn `to`. Hai đầu mút đều được tính là nằm trong khoảng.

## Phép so sánh cho mảng

| Phép so sánh | Đối chiếu | Đúng khi |
| --- | --- | --- |
| `@similar` | Wordlist | Có ít nhất một phần tử Target bằng một từ trong Wordlist. |
| `@contains` | Chuỗi | Có ít nhất một phần tử Target bằng chính xác chuỗi cấu hình. |
| `@match` | Biểu thức chính quy | Có ít nhất một phần tử Target khớp biểu thức cấu hình. |
| `@search` | Wordlist biểu thức chính quy | Có ít nhất một phần tử Target khớp một biểu thức trong Wordlist. |

Tên `@contains` có thể gây hiểu nhầm: mã nguồn hiện tại kiểm tra **phần tử mảng bằng chuỗi**, không kiểm tra chuỗi con bên trong phần tử.

Ví dụ Target trả về:

```json
["username", "password"]
```

- `@contains` với `password` là `true`.
- `@contains` với `pass` là `false`.
- `@match` với `^pass` là `true`.

## Phép so sánh cho số

| Phép so sánh | Đúng khi |
| --- | --- |
| `@equal` | Target bằng số cấu hình. |
| `@greaterThan` | Target lớn hơn số cấu hình. |
| `@lessThan` | Target nhỏ hơn số cấu hình. |
| `@greaterThanOrEqual` | Target lớn hơn hoặc bằng số cấu hình. |
| `@lessThanOrEqual` | Target nhỏ hơn hoặc bằng số cấu hình. |
| `@inRange` | `from <= target <= to`. |

Defender phân tích số theo `float64` và cho phép khoảng trắng ở hai đầu chuỗi số. Nếu Target hoặc giá trị đối chiếu không phân tích được, phép so sánh trả về `false`.

## Phép so sánh cho chuỗi

| Phép so sánh | Đối chiếu | Đúng khi |
| --- | --- | --- |
| `@mirror` | Chuỗi | Target bằng chính xác chuỗi cấu hình. |
| `@startsWith` | Chuỗi | Target bắt đầu bằng chuỗi cấu hình. |
| `@endsWith` | Chuỗi | Target kết thúc bằng chuỗi cấu hình. |
| `@check` | Wordlist | Target bằng một từ trong Wordlist. |
| `@regExp` | Biểu thức chính quy | Target khớp biểu thức cấu hình. |
| `@checkRegExp` | Wordlist biểu thức chính quy | Target khớp một biểu thức trong Wordlist. |

Các phép so sánh chuỗi phân biệt chữ hoa/thường. Muốn so sánh không phân biệt hoa/thường, hãy thêm Engine `lower` hoặc `upper` vào Target và chuẩn hóa giá trị đối chiếu tương ứng.

## Biểu thức chính quy

Biểu thức chính quy được Defender biên dịch bằng gói regexp của Go, tức cú pháp RE2. Biểu thức không hợp lệ không đẩy lỗi ra ngoài; phép so sánh chỉ trả về `false`.

Với phép so sánh dùng Wordlist biểu thức chính quy, mỗi dòng Wordlist là một biểu thức độc lập. Chỉ cần một biểu thức hợp lệ khớp là Rule đúng; dòng bị lỗi được xem như không khớp.

## Đảo kết quả với `is_inversed`

`is_inversed` được áp dụng ở bước Principle, sau khi phép so sánh đã trả kết quả:

```text
matched = comparator(target, expected)
if is_inversed:
    matched = !matched
```

Ví dụ `@mirror = admin` trả về `true`; nếu `is_inversed = true`, kết quả Rule dùng trong Principle là `false`.

## Gắn Action

Một Rule có thể gắn nhiều [Action](Action.md) qua bảng nối `rules_actions`, có trường `order`. Tuy nhiên Action không chạy ngay khi riêng Rule đó khớp.

Defender thực hiện theo hai bước:

1. Đánh giá toàn bộ Rule của Principle theo toán tử AND.
2. Chỉ khi tất cả đều đúng, chạy từng nhóm Action của các Rule theo thứ tự Rule và thứ tự Action.

Cách này ngăn Action của Rule đầu tạo tác động phụ trước khi biết Rule sau có thất bại hay không.

## Khóa do quan hệ

Rule có `is_locked`. Nó bị khóa khi đang gắn vào Principle. Target, Wordlist và Action liên quan cũng được Manager đồng bộ trạng thái khóa theo quan hệ sử dụng.

## Danh sách kiểm tra cấu hình

- Giai đoạn Rule phải trùng Target và Principle.
- Phép so sánh phải đúng kiểu dữ liệu cuối sau chuỗi Engine.
- Chọn Wordlist cho bốn phép so sánh dùng danh sách từ.
- Với `@inRange`, bảo đảm `from < to`.
- Kiểm tra biểu thức chính quy theo cú pháp RE2.
- Chỉ bật `is_inversed` khi thực sự muốn phủ định toàn bộ phép so sánh.
- Kiểm tra lại Principle sau khi đổi Target, phép so sánh, Wordlist hoặc Action.
