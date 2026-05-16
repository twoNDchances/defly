# Vận hành

Phần này gom các thao tác vận hành thường dùng khi chạy Defly bằng Docker
Compose.

## Khởi động, dừng và khởi động lại

Khởi động toàn bộ hệ thống:

```powershell
docker compose up -d --build
```

Xem trạng thái:

```powershell
docker compose ps
```

Khởi động lại:

```powershell
docker compose restart
```

Dừng container nhưng giữ dữ liệu:

```powershell
docker compose down
```

Dừng container và xóa volume có tên:

```powershell
docker compose down -v
```

Chỉ dùng `down -v` khi thật sự muốn xóa cơ sở dữ liệu và dữ liệu chạy.

## Xem nhật ký

Theo dõi nhật ký của các dịch vụ chính:

```powershell
docker compose logs -f mariadb orchestrator manager worker
```

Theo dõi riêng một dịch vụ:

```powershell
docker compose logs -f manager
docker compose logs -f orchestrator
docker compose logs -f worker
```

Nhật ký của Defender được triển khai động có thể nằm trong volume riêng hoặc
được xem qua chức năng theo dõi nhật ký trong Manager.

## Sao lưu và khôi phục cơ sở dữ liệu

Cơ sở dữ liệu MariaDB là nơi giữ cấu hình chính và dữ liệu vận hành. Cần sao
lưu trước khi nâng cấp image, đổi migration hoặc xóa volume.

Khi khôi phục, cần đảm bảo Manager, Orchestrator và Defender đang trỏ tới cùng
một cơ sở dữ liệu đã được khôi phục.

## Xoay vòng thông tin xác thực

Các thông tin nên xoay vòng định kỳ:

- mật khẩu cơ sở dữ liệu
- mật khẩu Orchestrator
- khóa API của Manager
- khóa hoặc chứng chỉ TLS
- mật khẩu người dùng quản trị

Sau khi đổi mật khẩu Orchestrator, phải cập nhật cả Manager và Orchestrator để
hai bên tiếp tục khớp nhau.

## Xoay vòng tệp TLS

Khi thay chứng chỉ TLS, kiểm tra:

- Manager có đọc được tệp `.crt` mới không
- đường dẫn trong `ORCHESTRATOR_TLS_CERT_FILE` còn đúng không
- thư mục `DEFENDER_SERVER_TLS_DIRECTORY` còn đúng không
- volume TLS của Defender có được gắn đúng không

Nếu đang phát triển cục bộ và chưa cần xác minh TLS, có thể tạm đặt biến bỏ qua
xác minh, nhưng không nên dùng cách này cho môi trường vận hành thật.

## Nâng cấp image

Trước khi nâng cấp image:

1. Sao lưu cơ sở dữ liệu.
2. Kiểm tra thay đổi trong `.env.example`.
3. Dựng lại image Defender nếu có thay đổi trong thư mục `defender`.
4. Chạy lại Docker Compose.
5. Kiểm tra Manager, Orchestrator và Defender.

Lệnh thường dùng:

```powershell
docker compose build defender
docker compose up -d --build
```

## Tăng số tiến trình hàng đợi

Có thể tăng số tiến trình hàng đợi khi có nhiều tác vụ nền:

```powershell
docker compose up -d --scale worker=3
```

Cần theo dõi tải cơ sở dữ liệu và số lượng tác vụ đang chờ để chọn số tiến
trình phù hợp.

## Kiểm tra sức khỏe hệ thống

Kiểm tra thực tế nên đi qua ba lớp:

1. Manager UI/API truy cập được.
2. Orchestrator nhận được yêu cầu từ tiến trình hàng đợi.
3. Proxy của Defender trả được phản hồi từ ứng dụng phía sau và ghi được quyết
   định hoặc báo cáo.

Nếu một lớp thất bại, xử lý lớp đó trước khi kiểm tra tiếp lớp sau.
