# Hướng dẫn Defender

Defender là chương trình Go chịu trách nhiệm nhận truy cập, áp dụng WAF và
chuyển tiếp yêu cầu tới ứng dụng phía sau.

## Thành phần chính

Defender gồm các phần chính:

- API điều khiển: điểm truy cập nội bộ để Manager kiểm tra hoặc điều khiển
  Defender.
- Máy chủ proxy: nhận truy cập từ máy khách và chuyển tiếp về ứng dụng phía
  sau.
- Bộ máy WAF: chạy các pha xử lý, khớp quy tắc và thực thi hành động.
- Quyết định: kết quả từng lần xử lý quy tắc hoặc hành động.
- Báo cáo: dữ liệu dùng để theo dõi và điều tra.
- Tệp nhật ký và lỗi: dữ liệu phục vụ kiểm tra khi Defender gặp sự cố.
- Tạo TLS: tạo chứng chỉ để Manager xác minh khi không bỏ qua kiểm tra TLS.

## API điều khiển

API điều khiển dùng cho Manager và các thành phần nội bộ. Không nên mở API này
ra mạng công khai nếu không có lớp bảo vệ phù hợp.

Cổng mặc định:

```text
9947
```

## Máy chủ proxy

Máy chủ proxy là cổng mà máy khách gửi truy cập vào. Defender xử lý truy cập
qua WAF rồi chuyển tiếp tới ứng dụng phía sau nếu yêu cầu được cho qua.

Cổng mặc định khi chạy thủ công:

```text
9948
```

Khi triển khai bằng Orchestrator, cổng proxy lấy từ bản ghi Defender trong
Manager.

## Bộ máy WAF

Bộ máy WAF đọc cấu hình từ cơ sở dữ liệu và áp dụng các nguyên tắc được gắn với
Defender. Mỗi nguyên tắc gồm nhiều quy tắc, mỗi quy tắc có điều kiện khớp và
hành động tương ứng.

## Pha xử lý yêu cầu

Defender có thể xử lý cả yêu cầu và phản hồi. Trong mỗi pha, Defender lấy dữ
liệu liên quan, so khớp với mẫu đã cấu hình và quyết định hành động cần thực
hiện.

## Khớp quy tắc

Quy tắc nên được viết đủ hẹp để tránh chặn nhầm truy cập hợp lệ. Khi kiểm thử
quy tắc mới, nên bắt đầu bằng hành động ghi nhận trước, sau đó mới chuyển sang
hành động chặn nếu kết quả phù hợp.

## Hành động

Hành động cho Defender biết cách xử lý khi quy tắc khớp. Các hành động thường
gặp:

- cho qua truy cập
- chặn truy cập
- ghi nhận để điều tra
- tạo báo cáo hoặc nhật ký

## Quyết định và báo cáo

Quyết định cho biết Defender đã xử lý một yêu cầu hoặc phản hồi như thế nào.
Báo cáo cung cấp dữ liệu rộng hơn để theo dõi tình hình và điều tra sự kiện.

Khi cần kiểm tra một sự kiện, nên bắt đầu từ quyết định, sau đó xem báo cáo và
nhật ký liên quan.

## Nhật ký và tệp lỗi

Nhật ký và tệp lỗi giúp xác định Defender có khởi động đúng không, có đọc được
cấu hình không, có kết nối được cơ sở dữ liệu không và có chuyển tiếp được về
ứng dụng phía sau không.

Khi Defender được Orchestrator triển khai, các tệp này thường nằm trong volume
riêng của từng Defender.

## Tạo TLS

Defender có thể tạo chứng chỉ TLS để Manager xác minh kết nối. Nếu
`DEFENDER_SERVER_TLS_SKIP_VERIFY=false`, Manager cần đọc được tệp `.crt` của
Defender trong thư mục được cấu hình.

## Biến môi trường

Khi chạy thủ công, Defender cần các biến sau:

```text
DATABASE_HOST
DATABASE_PORT
DATABASE_NAME
DATABASE_USER
DATABASE_PASS
DEFENDER_NAME
PROXY_BACKEND_URL
```

Khi chạy qua Orchestrator, các giá trị này được tạo từ bản ghi Defender và cấu
hình chung của hệ thống.
