# Hướng dẫn Defender

Defender là chương trình Go chạy API điều khiển, proxy ngược và tường lửa trong thời gian chạy. Khái niệm bản ghi triển khai được giải thích tại [Defender](CoreConcepts/Defender.md).

## Thành phần khi chạy

- Máy chủ điều khiển: API nội bộ, mặc định dùng cổng `9947`.
- Proxy: cổng nhận lưu lượng ứng dụng, mặc định `9948` khi chạy thủ công.
- Lõi tường lửa: thu thập giao dịch HTTP, chạy các giai đoạn, Principle, Action và Decision.
- Doctor: theo dõi tình trạng và phát hiện trạng thái bất thường.
- Bộ ghi nhật ký và lưu lỗi: ghi dữ liệu vận hành.

API điều khiển không nên mở công khai. Manager là bên gọi chính và có thể xác minh TLS theo [Cấu hình](Configuration.md#manager-và-defender).

## Khởi tạo giao dịch HTTP

Khi proxy nhận yêu cầu, Defender thu thập yêu cầu nguyên bản, nội dung, URL, cổng và siêu dữ liệu. Nội dung được đọc rồi phục hồi để máy chủ phía sau vẫn nhận được dữ liệu.

Giao dịch HTTP giữ:

- Yêu cầu và phản hồi hiện tại.
- Yêu cầu/phản hồi nguyên bản.
- Điểm và cấp độ.
- Biến trong quá trình chạy cho `setter`/`getter`.
- Kết quả cho phép, chặn, hủy, viết lại và các cờ phản hồi.

## Các giai đoạn yêu cầu

1. Giai đoạn `1`: toàn bộ yêu cầu.
2. Giai đoạn `2`: tiêu đề HTTP, tham số truy vấn và siêu dữ liệu của yêu cầu.
3. Giai đoạn `3`: nội dung và tệp của yêu cầu.

Ở mỗi giai đoạn, Defender chạy [Principle](CoreConcepts/Principle.md) đúng giai đoạn và cấp độ. Sau giai đoạn `3`, [Decision](CoreConcepts/Decision.md) hướng yêu cầu được đánh giá.

Nếu bị `deny`, Defender trả phản hồi chặn. Nếu bị `cancel`, kết nối bị đóng. Nếu được phép, yêu cầu có thể được viết lại rồi chuyển tới máy chủ phía sau.

## Các giai đoạn phản hồi

Defender thu thập phản hồi từ máy chủ phía sau, giải nén nội dung nếu cần để tường lửa đọc, rồi chạy:

4. Giai đoạn `4`: tiêu đề HTTP và siêu dữ liệu của phản hồi.
5. Giai đoạn `5`: nội dung phản hồi.
6. Giai đoạn `6`: toàn bộ phản hồi.

Sau Decision hướng phản hồi, Defender áp dụng việc viết lại tiêu đề HTTP hoặc nội dung, chặn, vô hiệu bộ nhớ đệm hoặc làm hết hạn cookie, rồi phục hồi cách mã hóa nội dung trước khi trả cho máy khách.

## Target và Engine

[Target](CoreConcepts/Target.md) chỉ đọc dữ liệu khi giai đoạn khớp. Nếu có [Pattern](CoreConcepts/Pattern.md), Defender gọi bộ trích xuất theo tên Pattern. Nếu Target dạng mảng dùng [Wordlist](CoreConcepts/Wordlist.md), mỗi dòng được xem là khóa cần đọc.

Giá trị sau đó đi qua [Engine](CoreConcepts/Engine.md). Chuỗi Engine dừng khi kiểu dữ liệu không khớp.

## Rule và Principle

[Rule](CoreConcepts/Rule.md) so sánh giá trị cuối với giá trị đối chiếu. Trong Principle, các Rule kết hợp bằng AND. Defender chỉ chạy Action sau khi toàn bộ Rule khớp.

Principle được chạy theo cấp độ tăng dần. Action `level` có thể mở rộng hoặc thu hẹp phần Principle còn lại trong giai đoạn mà không chạy lại phần đã qua.

## Action và Decision

[Action](CoreConcepts/Action.md) chạy trong Principle và có thể ghi nhật ký/báo cáo, đặt biến, cộng điểm hoặc chặn ngay. [Decision](CoreConcepts/Decision.md) chạy sau Principle và dùng tổng điểm.

Sự phân biệt này giúp chính sách tách việc phát hiện khỏi phán quyết: nhiều Rule có thể cộng điểm trước khi một Decision quyết định cho phép, chặn hoặc viết lại dữ liệu.

## Report và tệp điều tra

Action `report` ghi [Report](CoreConcepts/Report.md) vào cơ sở dữ liệu. Decision `save` ghi yêu cầu nguyên bản vào `storage/requests`. Nhật ký tường lửa mặc định có thể nằm ở `storage/logs/firewall.log`.

Các dữ liệu này có thể chứa thông tin bí mật và nội dung người dùng; xem [Bảo mật](Security.md#dữ-liệu-waf-và-quyền-riêng-tư).

## Chạy thủ công

Xem [Cài đặt Defender thủ công](Installation.md#4-defender). Cơ sở dữ liệu phải được Manager chạy migration và tạo dữ liệu khởi đầu trước.

## Kiểm thử thay đổi

Từ thư mục `defender`:

```powershell
go test ./...
```

Khi sửa firewall, chạy tối thiểu:

```powershell
go test ./internal/firewall/...
```

Thay đổi Pattern, phép so sánh, Action hoặc Decision cần kiểm thử cả dữ liệu đầu vào, kết quả giao dịch HTTP và dữ liệu báo cáo.
