# Tổng quan

Defly là một hệ thống tường lửa ứng dụng web (WAF) mã nguồn mở. Hệ thống này
giúp quản lý chính sách bảo vệ, triển khai Defender và áp dụng các chính sách
đó lên luồng truy cập đi qua proxy.

## Defly giải quyết vấn đề gì

Defly gom giao diện quản trị, bộ điều phối triển khai và Defender vào cùng một
kho mã nguồn. Cách tổ chức này giúp việc cấu hình quy tắc, triển khai Defender
và vận hành WAF nhất quán hơn giữa môi trường phát triển cục bộ và môi trường
Docker.

Các tình huống sử dụng chính:

- Quản lý người dùng, nhóm, quyền và khóa API cho hệ thống bảo vệ ứng dụng web.
- Tạo mục tiêu, bộ máy xử lý, mẫu khớp, danh sách từ, hành động, quy tắc và
  nguyên tắc cho WAF.
- Triển khai nhiều Defender từ Manager thông qua Orchestrator.
- Đưa truy cập đi qua Defender để ghi quyết định, báo cáo, nhật ký và áp dụng
  hành động bảo vệ.
- Vận hành toàn bộ hệ thống bằng Docker Compose hoặc cài thủ công khi phát
  triển từng dịch vụ.

## Các dịch vụ chính

- `manager`: ứng dụng Laravel/Filament dùng để quản trị người dùng, quyền,
  mục tiêu, bộ máy xử lý, quy tắc, nguyên tắc, quyết định, Defender, báo cáo và
  dòng thời gian.
- `orchestrator`: dịch vụ Django ASGI được Manager gọi để triển khai, theo dõi
  nhật ký và hủy container Defender thông qua Docker.
- `defender`: chương trình Go chạy API điều khiển Defender, bộ máy WAF và proxy
  ngược cho lưu lượng cần bảo vệ.
- `mariadb`: cơ sở dữ liệu dùng chung cho Manager, Orchestrator, tiến trình hàng
  đợi và các Defender đã triển khai.
- `worker`: tiến trình hàng đợi Laravel xử lý các tác vụ nền, đặc biệt là triển
  khai, hủy triển khai và theo dõi nhật ký từ Manager sang Orchestrator.

## Luồng hoạt động tổng quan

1. Quản trị viên cấu hình mục tiêu, bộ máy xử lý, mẫu khớp, hành động, quy tắc
   và nguyên tắc trong Manager.
2. Manager lưu cấu hình vào MariaDB và tạo tác vụ nền khi cần triển khai
   Defender.
3. Tiến trình hàng đợi gọi Orchestrator bằng Basic Auth và gửi kèm email của
   người thực hiện.
4. Orchestrator dùng Docker API để tạo hoặc cập nhật container Defender.
5. Defender đọc cấu hình từ cơ sở dữ liệu, mở API điều khiển và mở cổng proxy.
6. Lưu lượng đi qua proxy của Defender, bộ máy WAF khớp quy tắc rồi ghi quyết
   định và báo cáo.
