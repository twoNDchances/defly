# Cấu hình

Trang này giải thích cách Defly chia cấu hình và những giá trị phải khớp giữa các
service. Danh sách đầy đủ tên biến, mặc định và validation chỉ nằm trong
[Biến môi trường](Environment-Variables.md).

## Nguồn cấu hình

| Runtime | Nguồn | Trách nhiệm |
| --- | --- | --- |
| Stack Docker Compose | `.env` ở root | Image, port publish, credential dùng chung, database, volume và giá trị inject vào container |
| Manager chạy thủ công | `manager/.env` | Laravel, queue, mail, Manager API và kết nối outbound |
| Orchestrator chạy thủ công | `orchestrator/.env` | Django, Docker access và internal API |
| Defender | Bản ghi Defender và deployment environment | Backend, control server, proxy, logging và scoring |

Không quản lý cùng một deployment qua nhiều nguồn. Với Compose, `.env` ở root và
kết quả `docker compose config` là nguồn chuẩn; khi chạy thủ công, dùng `.env` của
từng service.

## Contract giữa các service

### Database

Manager sở hữu schema và migration. Manager, Orchestrator và mọi Defender phải trỏ
tới cùng database server, database name và credential tương thích. Luôn chạy migration
của Manager trước code cần schema mới hơn.

### Manager và Orchestrator

Hai phía phải thống nhất:

- username/password Basic Auth
- path deployment
- HTTP method deploy, follow và cancel
- tên header email người thực hiện
- chính sách xác minh TLS

Sai khác ở đây là lỗi transport/cấu hình, không phải lỗi policy. Tên biến hai phía
được ánh xạ tại
[Ánh xạ giữa các dịch vụ](Environment-Variables.md#ánh-xạ-giữa-các-dịch-vụ).

### Orchestrator và Docker

Orchestrator cần quyền Docker đặc quyền, tên Defender image và key volume TLS dùng
chung. Trong Compose, nó lấy project/network context từ chính container của mình rồi
gán context đó cho Defender động. Bảo vệ Docker access theo
[Bảo mật](Security.md#tiến-trình-nền-docker).

### Manager và Defender

Job điều khiển của Manager nhận diện Defender theo tên và xác minh certificate control
server khi bật TLS verification. Vì vậy quy tắc đặt tên certificate và storage dùng
chung phải nhất quán giữa Manager, Orchestrator và Defender.

## Lựa chọn theo môi trường

Local có thể dùng TLS self-signed và credential phát triển trên host tin cậy.
Production là một security profile riêng, không phải tập hợp các thay đổi biến rời
rạc. Hãy áp dụng đầy đủ kiểm soát tại
[Bảo mật](Security.md#danh-sách-kiểm-tra-môi-trường-thật), thay vì chép một checklist
thiếu từ trang này.

## Áp dụng thay đổi

1. Tìm biến và ràng buộc trong [Biến môi trường](Environment-Variables.md).
2. Xác định mọi phía của cross-service contract.
3. Sửa đúng nguồn cấu hình chịu trách nhiệm.
4. Kiểm tra `docker compose config` nếu dùng Compose.
5. Chỉ restart static service bị ảnh hưởng.
6. Redeploy Defender nếu image, environment, network, volume hoặc port publish đổi.
7. Xác minh đúng tầng theo [Vận hành](Operations.md#kiểm-tra-sức-khỏe-theo-từng-lớp).
