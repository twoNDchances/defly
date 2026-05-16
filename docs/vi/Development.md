# Phát triển

Phần này mô tả các lệnh kiểm tra và quy ước phát triển cơ bản cho từng dịch vụ
trong Defly.

## Thiết lập phát triển cục bộ

Có thể phát triển theo hai cách:

- Chạy toàn bộ hệ thống bằng Docker Compose.
- Chạy từng dịch vụ thủ công và chỉ dùng Docker cho các phần cần thiết.

Khi chạy thủ công, vẫn nên dùng cùng một cơ sở dữ liệu cho Manager,
Orchestrator và Defender để mô phỏng đúng luồng triển khai.

## Kiểm tra Manager

Từ thư mục `manager`:

```powershell
cd manager
php artisan test
```

Các lệnh Laravel thường dùng:

```powershell
php artisan migrate
php artisan db:seed
php artisan optimize
php artisan queue:work --tries=3
```

Khi thay đổi giao diện hoặc tài nguyên phía trước, chạy:

```powershell
npm run build
```

## Kiểm tra Orchestrator

Từ thư mục `orchestrator`:

```powershell
cd orchestrator
uv run python manage.py check
uv run python -m ruff check .
```

Khi chạy phát triển cục bộ:

```powershell
$env:DJANGO_SETTINGS_MODULE = "configs.development"
uv run uvicorn configs.asgi:application --reload --host 0.0.0.0 --port 8000
```

## Kiểm tra Defender

Từ thư mục `defender`:

```powershell
cd defender
go test ./...
```

Khi phát triển Defender độc lập, cần đảm bảo cơ sở dữ liệu đã được Manager chạy
migration và seed.

## Sinh mã

Nếu thay đổi phần có sinh mã, cần chạy đúng lệnh của dịch vụ tương ứng trước
khi kiểm thử. Không nên sửa tệp sinh tự động bằng tay nếu có lệnh sinh mã rõ
ràng trong kho mã nguồn.

## Quy ước khi thêm đối tượng mới

Khi thêm model hoặc tài nguyên mới trong Manager:

1. Tạo migration và model.
2. Thêm tài nguyên Filament nếu cần quản trị qua giao diện.
3. Thêm quyền phù hợp.
4. Thêm dữ liệu seed nếu đối tượng cần giá trị mặc định.
5. Kiểm tra tác động tới Orchestrator hoặc Defender nếu dữ liệu được dùng khi
   triển khai.

## Quy ước khi thêm quy tắc hoặc hành động Defender

Khi thêm quy tắc hoặc hành động mới cho Defender:

1. Xác định dữ liệu cấu hình cần lưu trong Manager.
2. Đảm bảo Defender đọc được dữ liệu đó từ cơ sở dữ liệu.
3. Thêm kiểm thử cho phần khớp quy tắc và hành động.
4. Kiểm tra quyết định và báo cáo được ghi đúng.
5. Kiểm tra triển khai qua Orchestrator nếu thay đổi ảnh hưởng tới biến môi
   trường hoặc volume.

## Gợi ý kiểm thử trước khi gửi thay đổi

Tùy phạm vi thay đổi, nên chạy:

```powershell
cd manager
php artisan test
```

```powershell
cd orchestrator
uv run python manage.py check
uv run python -m ruff check .
```

```powershell
cd defender
go test ./...
```

Nếu thay đổi tài liệu, nên kiểm tra liên kết Markdown và đọc lại các lệnh mẫu.
