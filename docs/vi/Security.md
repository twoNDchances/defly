# Bảo mật

Defly vừa quản lý chính sách bảo mật vừa có quyền điều khiển Docker, vì vậy cần bảo vệ cả lớp điều khiển lẫn dữ liệu WAF.

## Ranh giới tin cậy

| Ranh giới | Cơ chế chính |
| --- | --- |
| Người dùng -> giao diện Manager | Xác thực Laravel/Filament và [Permission](CoreConcepts/Permission.md). |
| Máy khách -> Manager API | Basic Auth và [Key](CoreConcepts/Key.md). |
| Worker -> Orchestrator | Basic Auth, danh sách bên gọi được phép và TLS. |
| Manager -> Defender API | Authorization nội bộ và TLS. |
| Orchestrator -> Docker | Docker socket hoặc API đặc quyền cao. |
| Máy khách -> Defender proxy | Chính sách WAF và cấu hình TLS/proxy. |

## Người dùng, nhóm và quyền

Áp dụng nguyên tắc quyền tối thiểu qua [Group](CoreConcepts/Group.md). Tách ít nhất các vai trò:

- Quản trị người dùng/khóa.
- Xây dựng và kiểm tra chính sách.
- Áp dụng chính sách và triển khai Defender.
- Xem báo cáo chứa dữ liệu nhạy cảm.

Rà soát định kỳ người dùng không còn hoạt động và thành viên trong nhóm.

Dùng [Guard](CoreConcepts/Guard.md) khi chỉ một nhóm người vận hành cụ thể được phép điều khiển một Defender cụ thể. Guard cố ý hẹp hơn Permission: chủ sở hữu Defender vẫn được thao tác trên Defender của mình, nhưng User root khác hoặc User có `Defender:all` vẫn phải thuộc Guard khớp và còn hiệu lực khi Defender đã được bảo vệ bằng Guard.

## Thông tin xác thực và bí mật

Không đưa các dữ liệu sau vào kho mã nguồn:

- `.env` thật.
- Mật khẩu cơ sở dữ liệu hoặc Orchestrator.
- API token.
- Khóa bí mật TLS.
- Tệp bí mật Django.
- Bản sao cơ sở dữ liệu hoặc báo cáo từ môi trường thật.

Token Manager API được băm khi lưu. Vì không thể đọc lại token gốc, hãy phân phối một lần qua kênh an toàn và thu hồi khi mất kiểm soát.

## TLS giữa các dịch vụ

Trong môi trường thật, đặt:

```text
ORCHESTRATOR_TLS_SKIP_VERIFY=false
DEFENDER_SERVER_TLS_SKIP_VERIFY=false
```

Manager phải đọc đúng chứng chỉ `.crt`. Khóa bí mật chỉ cần ở máy chủ tương ứng. Không dùng chứng chỉ tự ký thiếu quản lý vòng đời cho hệ thống phân tán lâu dài.

## Tiến trình nền Docker

Tiến trình Docker có thể tạo container đặc quyền, gắn hệ thống tệp và đọc ổ dữ liệu. Cần xem Orchestrator như một thành phần có quyền cao trên máy chủ.

- Ưu tiên Unix socket trên máy chủ được kiểm soát.
- Không mở TCP `2375` ra mạng công khai.
- Nếu dùng TCP, đặt sau mạng riêng và TLS/mTLS phù hợp.
- Giới hạn máy chủ có thể gọi Orchestrator.
- Theo dõi container và image ngoài dự kiến.

## Dữ liệu WAF và quyền riêng tư

[Report](CoreConcepts/Report.md), hành động ghi nhật ký và Decision `save` có thể lưu tiêu đề HTTP, cookie, token, nội dung hoặc dữ liệu cá nhân.

- Chỉ thu thập phần cần điều tra.
- Hạn chế quyền xem báo cáo và ổ dữ liệu.
- Đặt thời hạn lưu giữ và quy trình xóa.
- Mã hóa bản sao lưu.
- Không gửi dữ liệu nhạy cảm qua Action `request` nếu điểm nhận không đáng tin.

## An toàn chính sách

Một [Rule](CoreConcepts/Rule.md) hoặc [Decision](CoreConcepts/Decision.md) sai có thể chặn lưu lượng hợp lệ.

Quy trình khuyến nghị:

1. Kiểm thử Target và Engine bằng dữ liệu đại diện.
2. Bắt đầu bằng Action `log`, `report` hoặc `suspect`.
3. Kiểm tra [Principle](CoreConcepts/Principle.md).
4. Áp dụng trên môi trường thử nghiệm.
5. Theo dõi trường hợp nhận diện nhầm.
6. Chỉ chuyển sang chặn/hủy khi có tiêu chí khôi phục rõ ràng.

## Danh sách kiểm tra môi trường thật

- `APP_DEBUG=false`.
- Bí mật và mật khẩu đủ mạnh, có lịch xoay vòng.
- Bật xác minh TLS giữa các dịch vụ.
- Docker API không mở công khai.
- Cơ sở dữ liệu và ổ dữ liệu được sao lưu.
- Báo cáo/yêu cầu nguyên bản có thời hạn lưu giữ.
- Người dùng/nhóm/quyền được rà soát.
- Thành viên Guard và thời hạn Guard của Defender ở môi trường thật được rà soát.
- Principle đã có trạng thái `passed` và Decision được kiểm thử ở cả hai hướng.
- Có cách bỏ qua hoặc khôi phục chính sách khi chặn nhầm.
