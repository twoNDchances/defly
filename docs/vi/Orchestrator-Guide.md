# Hướng dẫn Orchestrator

Orchestrator nhận yêu cầu triển khai từ Manager hoặc tiến trình hàng đợi, sau
đó điều khiển Docker để chạy container Defender.

## Trách nhiệm chính

Orchestrator chịu trách nhiệm:

- xác thực Manager hoặc tiến trình hàng đợi bằng Basic Auth
- kiểm tra máy gọi có nằm trong danh sách cho phép hay không
- tạo và cập nhật vòng đời triển khai
- triển khai Defender từ `SERVER_DEFENDER_IMAGE`
- gắn volume TLS, lỗi và nhật ký cho Defender
- gắn Defender vào mạng Docker phù hợp
- theo dõi nhật ký hoặc hủy triển khai khi Manager yêu cầu
- ghi lỗi đủ rõ để Manager hiển thị và cho phép thử lại khi cần

## Vòng đời triển khai

Một lần triển khai thường đi qua các trạng thái:

- đang chờ xử lý
- đang xử lý
- thành công
- thất bại

Manager hiển thị các trạng thái này để người vận hành biết yêu cầu đang ở bước
nào. Nếu trạng thái thất bại, cần xem thông báo lỗi và nhật ký Orchestrator
trước khi thử lại.

## Theo dõi nhật ký

Manager có thể yêu cầu Orchestrator theo dõi nhật ký của Defender hoặc nhật ký
triển khai. Chức năng này giúp kiểm tra nhanh lý do Defender không khởi động,
không mở được cổng hoặc không đọc được cấu hình.

## Hủy triển khai

Khi Manager gửi yêu cầu hủy, Orchestrator cần dừng hoặc gỡ container Defender
tương ứng và cập nhật trạng thái về cơ sở dữ liệu. Dữ liệu volume có thể được
giữ lại để phục vụ điều tra hoặc tái sử dụng, tùy cấu hình và cách triển khai.

## Truy cập Docker API

`SERVER_DOCKER_BASE_URL` phải trỏ tới Docker daemon mà Orchestrator có thể truy
cập. Ví dụ:

```text
tcp://localhost:2375
unix:///var/run/docker.sock
```

Chỉ bật TCP Docker API trên máy phát triển cục bộ đáng tin cậy. Docker API có
quyền rất mạnh, nên không nên mở ra mạng không kiểm soát.

## Volume Docker

Orchestrator dùng:

- volume TLS chung từ `SERVER_DEFENDER_TLS_VOLUME`
- volume lỗi riêng cho từng Defender
- volume nhật ký riêng cho từng Defender

Volume TLS chung cần khớp với cấu hình mà Manager có thể đọc khi cần xác minh
TLS của Defender.

## Mạng Docker

Defender cần nằm trong mạng Docker mà Orchestrator và các dịch vụ liên quan có
thể truy cập. Khi chạy bằng Docker Compose, mạng này thường được tạo tự động
theo tên dự án Compose.

Nếu Defender không truy cập được cơ sở dữ liệu hoặc ứng dụng phía sau, hãy kiểm
tra mạng Docker trước khi kiểm tra cấu hình WAF.

## Xác thực API

Manager gọi Orchestrator bằng Basic Auth. Các giá trị sau phải khớp giữa hai
bên:

```text
ORCHESTRATOR_USERNAME
ORCHESTRATOR_PASSWORD
SERVER_USERNAME
SERVER_PASSWORD
```

Nếu bật xác minh TLS, Manager cũng phải đọc được chứng chỉ của Orchestrator.

## Lỗi và phục hồi

Khi triển khai thất bại, kiểm tra theo thứ tự:

1. Orchestrator có truy cập được Docker daemon không.
2. Image `SERVER_DEFENDER_IMAGE` có tồn tại không.
3. Cổng proxy của Defender có bị trùng không.
4. Volume TLS, lỗi và nhật ký có tạo được không.
5. Defender có đọc được cơ sở dữ liệu không.
