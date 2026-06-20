# Phát triển

Defly là một kho mã nguồn chung gồm Laravel, Django và Go. Khi thay đổi giao kèo giữa các dịch vụ, cần cập nhật đồng thời mã nguồn, kiểm thử, `.env.example` và tài liệu.

## Thiết lập

Có thể chạy toàn bộ hệ thống bằng Compose hoặc chạy từng dịch vụ theo [Cài đặt thủ công](Installation.md#cài-thủ-công). Dù chọn cách nào, ba dịch vụ phải dùng lược đồ cơ sở dữ liệu do Manager quản lý qua migration.

## Manager

```powershell
cd manager
php artisan test
npm run build
```

Lệnh thường dùng:

```powershell
php artisan migrate
php artisan db:seed
php artisan queue:work --tries=3
```

Khi thay biểu mẫu Filament, hãy cập nhật cả bộ kiểm tra dữ liệu, phần ánh xạ dữ liệu, yêu cầu/bộ điều khiển API và kiểm thử tương ứng.

## Orchestrator

```powershell
cd orchestrator
uv run python manage.py check
uv run python -m ruff check .
uv run python manage.py test
```

Kiểm thử triển khai cần bao phủ tham số Docker, nhãn Compose, mạng, ổ dữ liệu và việc dọn dẹp khi lỗi.

## Defender

```powershell
cd defender
go test ./...
```

Riêng firewall:

```powershell
go test ./internal/firewall/...
```

Chạy `gofmt` cho tệp Go đã sửa.

## Thay đổi chuỗi xử lý WAF

Các lớp phụ thuộc theo thứ tự:

```text
Pattern/Wordlist -> Target -> Engine -> Rule -> Action -> Principle -> Decision
```

Khi thêm hoặc đổi một thành phần:

1. Cập nhật kiểu liệt kê, quy tắc kiểm tra và biểu mẫu/API Manager.
2. Cập nhật phần ánh xạ dữ liệu để JSON cấu hình có cùng cấu trúc.
3. Cập nhật Defender để đọc đúng cấu trúc đó trong quá trình chạy.
4. Thêm kiểm thử đơn vị cho kiểu dữ liệu, giai đoạn và trường hợp biên.
5. Thêm kiểm thử tích hợp cho giao dịch HTTP.
6. Kiểm tra [Report](CoreConcepts/Report.md) vẫn mô tả đúng kết quả.
7. Cập nhật trang khái niệm tương ứng.

### Pattern

Tên trong `PatternSeeder.php` phải có bộ trích xuất tương ứng trong Defender. Kiểm thử cần xác nhận giai đoạn, loại và kiểu dữ liệu.

### Engine

Manager và Defender phải thống nhất kiểu dữ liệu đầu vào/đầu ra cùng tham số tùy chọn/mặc định.

### Phép so sánh

Khi thêm phép so sánh, cần cập nhật kiểu liệt kê trong Manager, cách Rule chọn theo kiểu dữ liệu, phần so sánh của Defender và các giá trị khớp trong báo cáo.

### Action và Decision

Xác định rõ Action có dừng chuỗi hay không, chạy đồng bộ hay bất đồng bộ, tác động lên yêu cầu hay phản hồi và cấu hình nào bắt buộc.

## Thay đổi API hoặc biến môi trường

Khi đổi đường dẫn, phương thức, tiêu đề HTTP hoặc biến giữa hai dịch vụ:

- Cập nhật cả bên gọi và bên nhận.
- Cập nhật `.env.example` gốc và của dịch vụ.
- Cập nhật kiểm thử giao kèo.
- Cập nhật [Cấu hình](Configuration.md) và [Tham chiếu API](API-Reference.md).

## Sinh mã

Không sửa tệp được sinh tự động khi dự án có lệnh sinh mã. Với Ent hoặc tệp sinh ra tương tự, hãy sửa lược đồ nguồn rồi chạy công cụ sinh mã, sau đó xem lại phần thay đổi được tạo.

## Danh sách kiểm tra trước khi gửi thay đổi

- Kiểm thử trong phạm vi thay đổi đã đạt.
- Công cụ định dạng/kiểm tra mã đã chạy.
- Không có bí mật hoặc dữ liệu trong quá trình chạy nằm trong phần thay đổi.
- Migration và seed có đường nâng cấp rõ.
- Giao kèo Manager/Orchestrator/Defender đồng bộ.
- Liên kết Markdown không hỏng.
- Thuật ngữ mới có trang giải thích hoặc liên kết tới định nghĩa trước đó.
