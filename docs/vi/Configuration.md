# Cấu hình

Trang này giải thích cách Defly chia cấu hình và những giá trị phải khớp giữa các dịch vụ. Danh sách đầy đủ tên biến, mặc định và kiểm tra dữ liệu nằm trong [Biến môi trường](Environment-Variables.md).

## Nguồn cấu hình

| Runtime | Nguồn | Trách nhiệm |
| --- | --- | --- |
| Stack Docker Compose | `.env` ở root | Image, port publish, credential dùng chung, database, volume và giá trị inject vào container |
| Manager chạy thủ công | `manager/.env` | Laravel, queue, mail, Manager API và kết nối outbound |
| Orchestrator chạy thủ công | `orchestrator/.env` | Django, quyền Docker, nhà cung cấp AI và API nội bộ |
| Defender | Bản ghi Defender và deployment environment | Backend, control server, proxy, logging và scoring |

Không quản lý cùng một triển khai qua nhiều nguồn. Với Compose, `.env` ở root và kết quả `docker compose config` là nguồn chuẩn; khi chạy thủ công, dùng `.env` của từng dịch vụ.

## Contract giữa các service

### Database

Manager sở hữu lược đồ và migration. Manager, Orchestrator và mọi Defender phải trỏ tới cùng máy chủ, tên cơ sở dữ liệu và thông tin đăng nhập tương thích. Luôn chạy migration của Manager trước mã nguồn cần lược đồ mới hơn.

### Manager và Orchestrator

Hai phía phải thống nhất:

- username/password Basic Auth
- path deployment
- path assistant
- HTTP method deploy, follow và cancel
- HTTP method chat
- tên header email người thực hiện
- chính sách xác minh TLS

Sai khác ở đây là lỗi kết nối/cấu hình, không phải lỗi chính sách. Tên biến hai phía được ánh xạ tại [Ánh xạ giữa các dịch vụ](Environment-Variables.md#ánh-xạ-giữa-các-dịch-vụ).

### Orchestrator và Docker

Orchestrator cần quyền Docker đặc quyền, tên image Defender và khóa volume TLS dùng chung. Trong Compose, nó lấy project/network context từ chính container của mình rồi gán context đó cho Defender động. Bảo vệ Docker access theo [Bảo mật](Security.md#tiến-trình-nền-docker).

### Orchestrator và nhà cung cấp AI

Orchestrator gọi nhà cung cấp AI cho trang trợ lý của Manager, nên `AI_API_KEY`, `AI_BASE_URL`, `AI_MODEL`, timeout và giới hạn tin nhắn phải được cấu hình ở phía Orchestrator. Manager chỉ gửi ID hội thoại và email người thực hiện.

### Manager và Defender

Job điều khiển của Manager nhận diện Defender theo tên và xác minh chứng chỉ control server khi bật TLS verification. Vì vậy quy tắc đặt tên chứng chỉ và storage dùng chung phải nhất quán giữa Manager, Orchestrator và Defender.

## Lựa chọn theo môi trường

Local có thể dùng TLS tự ký và thông tin đăng nhập phát triển trên host tin cậy. Production là một hồ sơ bảo mật riêng, không phải vài biến rời rạc. Hãy áp dụng đầy đủ kiểm soát tại [Bảo mật](Security.md#danh-sách-kiểm-tra-môi-trường-thật).

## Áp dụng thay đổi

1. Tìm biến và ràng buộc trong [Biến môi trường](Environment-Variables.md).
2. Xác định mọi phía của cross-service contract.
3. Sửa đúng nguồn cấu hình chịu trách nhiệm.
4. Kiểm tra `docker compose config` nếu dùng Compose.
5. Chỉ restart static service bị ảnh hưởng.
6. Redeploy Defender nếu image, environment, network, volume hoặc port publish đổi.
7. Xác minh đúng tầng theo [Vận hành](Operations.md#kiểm-tra-sức-khỏe-theo-từng-lớp).
