# Guard

Guard giới hạn ai được thao tác trên các [Defender](Defender.md) đã chọn. Đây là lớp truy cập thứ hai sau [Permission](Permission.md): Permission trả lời “chủ thể có loại hành động này không”, còn Guard trả lời “User hiện tại có được thao tác trên Defender cụ thể này không”.

Guard không thay đổi cách WAF xử lý lưu lượng. Guard bảo vệ các thao tác quản trị và đường điều khiển như triển khai Defender, hủy triển khai, theo dõi nhật ký, áp dụng/thu hồi chính sách và cài đặt/tạm ngưng Decision.

## Các trường cấu hình

| Trường | Bắt buộc | Ràng buộc và ý nghĩa |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, chữ thường dùng dấu gạch ngang, tối đa 255 ký tự. |
| `description` | Không | Ngữ cảnh quản trị: vì sao Guard này tồn tại. |
| `expired_at` | Không | Thời điểm hết hạn. `null` nghĩa là Guard không hết hạn. |

Guard cũng có UUID, `created_by`, thời điểm tạo/cập nhật và có thể gắn [Label](Label.md).

## Quan hệ

| Quan hệ | Bảng nối | Ý nghĩa |
| --- | --- | --- |
| `users` | `guards_users` | User được Guard này cho phép. |
| `defenders` | `guards_defenders` | Defender được Guard này bảo vệ. |
| `labels` | `labels_resources` | Siêu dữ liệu phân loại. |

Guard dựa trên User. [Group](Group.md), [Key](Key.md) và Permission có thể cấp loại hành động, nhưng tự chúng không thỏa điều kiện Guard của Defender.

## Quy tắc thực thi

Với một thao tác trên Defender:

```text
if defender không có Guard:
    tiếp tục theo Permission và quy tắc quy trình thông thường
else if User hiện tại/người yêu cầu là chủ sở hữu Defender qua defenders.created_by:
    cho phép
else if User hiện tại/người yêu cầu thuộc ít nhất một Guard chưa hết hạn gắn với Defender:
    cho phép
else:
    từ chối
```

Guard chưa hết hạn là Guard có `expired_at = null` hoặc `expired_at` nằm sau thời điểm hiện tại. Nếu một Defender có nhiều Guard, chỉ cần khớp một Guard còn hiệu lực là đủ.

User được ghi trong `defenders.created_by` là chủ sở hữu Defender và không bị Guard giới hạn với Defender đó. User root và Permission `Defender:all` nếu không phải chủ sở hữu vẫn phải thỏa Guard khi Defender đã được bảo vệ bằng Guard. Đây là chủ ý thiết kế: Guard thu hẹp các thao tác Defender có tác động cao về đúng nhóm người vận hành được phê duyệt mà không khóa người tạo khỏi Defender của họ.

Guard cũng giới hạn phạm vi nhìn thấy Defender. Defender không gắn Guard hiển thị công khai với User có quyền liệt kê/xem Defender. Defender có Guard chỉ hiển thị với chủ sở hữu hoặc User thuộc ít nhất một Guard khớp và chưa hết hạn.

## Guard được kiểm tra ở đâu

Guard được kiểm tra xuyên suốt đường điều khiển:

- Giao diện Manager và Manager API khi xác thực quyền cho các thao tác vòng đời và điều khiển chính sách của Defender.
- Tác vụ trong Manager trước khi gọi Orchestrator hoặc API điều khiển Defender.
- Điểm cuối triển khai của Orchestrator dựa trên User thực hiện.
- API điều khiển Defender trước khi đồng bộ chính sách.

Nếu một tác vụ được đưa vào hàng đợi trước khi thành viên Guard thay đổi, tác vụ vẫn kiểm tra lại người yêu cầu lúc chạy. Vì vậy việc gỡ User khỏi Guard còn hiệu lực hoặc đặt Guard hết hạn có thể chặn thao tác đang chờ.

## Gợi ý thiết kế

- Dùng Guard không hết hạn cho ranh giới sở hữu ổn định, ví dụ một nhóm ứng dụng.
- Dùng `expired_at` cho quyền tạm thời trong sự cố hoặc đợt phát hành.
- Chỉ gắn Defender cần thêm ranh giới này. Defender không có Guard vẫn dùng Permission và quy tắc quy trình thông thường.
- Giữ đồng hồ Manager, Orchestrator và Defender tương đối đồng bộ vì cả ba có thể đánh giá thời điểm hết hạn.
- Không dùng Guard thay cho Permission. User vẫn cần Permission của hành động Defender tương ứng.

## Ví dụ

```text
guard production-edge-operators
users: alice@example.com, bob@example.com
defenders: checkout-prod, account-prod
expired_at: null
```

Alice và Bob có thể thao tác trên các Defender đó nếu họ cũng có Permission cần thiết. Một User thứ ba có `Defender:deploy` nhưng không thuộc `production-edge-operators` thì không thể triển khai `checkout-prod`.
