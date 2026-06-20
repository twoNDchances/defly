# Principle

Principle nhóm nhiều [Rule](Rule.md) trong cùng giai đoạn thành một điều kiện AND. Đây là đơn vị chính sách được gắn vào [Defender](Defender.md), kiểm tra và áp dụng/thu hồi độc lập.

```text
Principle = Rule 1 AND Rule 2 AND ... AND Rule n
```

## Các trường cấu hình

| Trường | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, viết thường theo dạng kebab-case, tối đa 255 ký tự. |
| `level` | Có | Cấp thực thi, số nguyên tối thiểu `1`; mặc định `1`. |
| `phase` | Có | Một trong sáu giai đoạn HTTP; mặc định `1`. |
| `validation_status` | Hệ thống | `pending`, `validating`, `failed` hoặc `passed`; chỉ đọc. |
| `validation_details` | Hệ thống | JSON kết quả kiểm tra; chỉ đọc. |
| `description` | Không | Ghi chú quản trị. |

Rule được gắn qua bảng nối `principles_rules` với trường `order`. Principle được gắn vào Defender qua `defenders_principles` với `order` và `is_applied`.

## Toán tử AND

Defender đánh giá Rule theo thứ tự gắn:

1. Bỏ qua phần tử Rule `nil` hoặc Rule không cùng giai đoạn.
2. Trích Target, chạy Engine và phép so sánh.
3. Đảo kết quả nếu `is_inversed = true`.
4. Nếu một Rule sai, dừng Principle và không chạy bất kỳ Action nào của Principle.
5. Nếu mọi Rule được xét đều đúng, chạy nhóm Action của từng Rule theo thứ tự.

Nếu Principle không có Rule, Defender bỏ qua. Việc gom Action lại đến cuối giúp tránh tác động phụ từ Rule đầu khi một Rule sau thất bại.

## Giai đoạn

Principle chỉ chạy tại giai đoạn đã khai báo. Rule và Target bên trong phải cùng giai đoạn; [phần kiểm tra](#kiểm-tra-hợp-lệ) phát hiện các quan hệ lệch giai đoạn.

Sáu giai đoạn được mô tả tại [Target](Target.md#sáu-giai-đoạn-http). Action `setter` và Target `getter` cho phép truyền dữ liệu từ giai đoạn trước sang giai đoạn sau trong cùng giao dịch HTTP.

## Cấp độ và thứ tự thực thi

Cấp độ phân tầng chính sách theo độ nghiêm ngặt. Giao dịch HTTP bắt đầu với `PROXY_VIOLATION_LEVEL`, mặc định `1`. Trong mỗi giai đoạn, Defender chạy cấp độ tăng dần từ `1` đến cấp độ hiện tại.

Điểm quan trọng: cấp độ không có nghĩa là “bắt đầu từ cấp hiện tại và bỏ cấp thấp”. Nếu giao dịch HTTP hiện có cấp `3`, bộ chạy lần lượt xét Principle cấp `1`, `2`, rồi `3`.

### Khi cấp độ tăng

Sau khi hoàn thành toàn bộ Principle của một cấp, bộ chạy đọc lại cấp độ hiện tại. Nếu Action `level` tăng từ `1` lên `3`, các Principle cấp `2` và `3` được thêm vào phạm vi chạy của giai đoạn hiện tại.

```text
level 1 -> action tăng lên 3 -> level 2 -> level 3
```

### Khi cấp độ giảm

Trước mỗi Principle và sau mỗi cấp, bộ chạy kiểm tra cấp độ hiện tại. Nếu cấp độ bị giảm thấp hơn cấp đang xét, bộ chạy dừng giai đoạn; những Principle đã chạy không chạy lại.

```text
level 1 -> level 2 -> action giảm về 1 -> dừng
```

Trong cùng một cấp, Principle giữ thứ tự nhận từ quan hệ Defender. Cấp độ khác điểm: cấp độ quyết định tập Principle được chạy; điểm là số được [Decision](Decision.md) dùng để phán quyết.

## Kiểm tra hợp lệ

Principle có bốn trạng thái:

| Trạng thái | Ý nghĩa |
| --- | --- |
| `pending` | Đã xếp hàng hoặc chờ bắt đầu kiểm tra. |
| `validating` | Tác vụ đang kiểm tra cấu trúc chính sách. |
| `failed` | Có ít nhất một lỗi hoặc quá trình kiểm tra gặp ngoại lệ. |
| `passed` | Không phát hiện lỗi cấu trúc. |

Quá trình kiểm tra chạy qua tác vụ hàng đợi. Khi bắt đầu, tác vụ đặt `validating`; khi xong, đặt `passed` hoặc `failed` và lưu `validation_details`. Nếu tác vụ phát sinh ngoại lệ, trạng thái là `failed` và phần chi tiết chứa lớp/thông báo lỗi.

Không thể yêu cầu kiểm tra lại khi Principle đang `pending` hoặc `validating`. Cập nhật/xóa Principle cũng bị chặn trong các trạng thái này.

### Nội dung được kiểm tra

Tác vụ kiểm tra tối thiểu:

- Giai đoạn Principle có hợp lệ hay không.
- Rule có tồn tại, cùng giai đoạn và phép so sánh hợp kiểu dữ liệu cuối hay không.
- Target có tồn tại, cùng giai đoạn và có tổ hợp loại/giai đoạn hợp lệ hay không.
- Target `full`/`meta` có Pattern bắt buộc hay không.
- Pattern có cùng giai đoạn, loại và kiểu dữ liệu với Target hay không; `getter` không được dùng Pattern.
- Target dạng mảng không có Pattern có Wordlist hợp lệ hay không.
- Chuỗi Engine có khớp kiểu dữ liệu đầu vào/đầu ra theo thứ tự hay không.
- Phép so sánh dùng Wordlist đã gắn Wordlist hay chưa.
- Tệp Wordlist có tồn tại/đọc được, JSON có đúng cấu trúc và số lượng hay không.
- Loại Action có thuộc danh mục hỗ trợ hay không.

`validation_details` chứa trạng thái, thời gian, danh sách lỗi có `code`, `message`, `context`, và phần tổng hợp số lượng Rule/Target/Engine/Action/Wordlist đã kiểm tra.

`passed` chỉ xác nhận tính nhất quán cấu trúc tại thời điểm kiểm tra; nó không chứng minh chính sách phát hiện đúng mọi lưu lượng thực tế.

## Gắn và áp dụng vào Defender

Vòng đời gồm hai bước riêng:

1. **Gắn:** tạo quan hệ Principle với Defender và xác định `order`.
2. **Áp dụng:** Manager gửi ID Principle sang Defender; khi thành công, bảng nối đặt `is_applied = true`.

Chỉ Principle có trạng thái `passed` mới được liệt kê/áp dụng qua luồng chuẩn. Defender cũng phải có `deployment_status = successful`.

`revoke` gửi yêu cầu xóa Principle khỏi quá trình chạy và chỉ hợp lệ khi Principle đã được áp dụng; khi thành công, `is_applied = false`. Gắn không có nghĩa là đã chạy trong Defender, và tháo quan hệ không nên thay thế thu hồi khi chính sách vẫn đang hoạt động.

Chi tiết giao tiếp nằm tại [Defender](Defender.md#quản-lý-chính-sách).

## Khóa do quan hệ

Principle có `is_locked` và bị khóa khi gắn với ít nhất một Defender. Các Rule gắn vào Principle cũng được khóa. Muốn chỉnh sửa cấu trúc, cần tháo quan hệ sử dụng theo đúng vòng đời và kiểm tra lại sau thay đổi.

## Danh sách kiểm tra vận hành

- Mọi Rule và Target phải cùng giai đoạn với Principle.
- Sắp Rule theo thứ tự dễ kiểm tra lỗi, dù logic là AND.
- Kiểm tra đến trạng thái `passed` trước khi gắn/áp dụng.
- Sau khi sửa Target, Engine, Rule, Action hoặc Wordlist, kiểm tra lại Principle liên quan.
- Phân biệt rõ gắn với áp dụng, thu hồi với tháo quan hệ.
- Kiểm tra Action `level` để tránh vô tình mở rộng hoặc dừng phạm vi Principle trong cùng giai đoạn.
