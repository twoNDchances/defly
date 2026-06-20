# Defender

Defender là một WAF kiêm proxy ngược bảo vệ một máy chủ phía sau. Bản ghi Defender trong Manager lưu cấu hình triển khai, biến môi trường, trạng thái vận hành và quan hệ chính sách; Orchestrator dùng bản ghi này để tạo container Defender.

## Các trường chính

| Trường | Bắt buộc | Ý nghĩa |
| --- | --- | --- |
| `name` | Có | Tên duy nhất, dạng chữ thường-ngăn-cách-bằng-gạch-ngang, tối đa 255 ký tự; đồng thời được dùng làm tên máy chủ/container. |
| `proxy_port` | Có | Cổng được công bố và cổng proxy trong container, từ `1` đến `65535`; mặc định `9948`. |
| `environment_variables` | Có | Đối tượng JSON chứa toàn bộ biến dùng chung/máy chủ/proxy. |
| `status` | Hệ thống | Sức khỏe khi chạy: `normal` hoặc `abnormal`. |
| `details` | Hệ thống | JSON giải thích trạng thái sức khỏe. |
| `deployment_status` | Hệ thống | Trạng thái triển khai. |
| `deployment_details` | Hệ thống | Kết quả hoặc lỗi triển khai/hủy. |
| `last_response_details` | Hệ thống | Phản hồi gần nhất khi áp dụng/thu hồi/cài/tạm ngưng chính sách. |
| `description` | Không | Ghi chú quản trị. |

Manager chia biến môi trường thành ba danh sách cố định. Không thể thêm, xóa hoặc đổi thứ tự khóa; khi lưu, ba nhóm được hợp nhất thành một đối tượng `environment_variables`.

## Biến dùng chung

| Biến | Mặc định | Ý nghĩa/ràng buộc |
| --- | --- | --- |
| `ABOUT_BANNER_ENABLE` | `true` | Bật banner thông tin; `true`/`false`. |
| `ERROR_FILE_ENABLE` | `false` | Ghi lỗi ra tệp. |
| `ERROR_DIRECTORY_PATH` | `storage/errors` | Thư mục lỗi; không được kết thúc bằng `.` hoặc `..`. |
| `DATABASE_HOST` | Theo DB Manager, dự phòng `mariadb` | Máy chủ cơ sở dữ liệu, không có khoảng trắng. |
| `DATABASE_PORT` | `3306` | Cổng `1..65535`. |
| `DATABASE_NAME` | Theo DB Manager | Cơ sở dữ liệu chứa chính sách/báo cáo. |
| `DATABASE_USER` | Theo DB Manager | Người dùng cơ sở dữ liệu. |
| `DATABASE_PASS` | Theo DB Manager | Mật khẩu cơ sở dữ liệu, có thể rỗng. |
| `DOCTOR_INTERVAL_UNIT` | `minute` | `second`, `minute` hoặc `hour`. |
| `DOCTOR_INTERVAL_COUNT` | `1` | Số nguyên `1..1000000`; nếu đơn vị là `second` thì tối thiểu `30`. |

Chuỗi kết nối cơ sở dữ liệu được Defender dùng để nạp chính sách và ghi [Report](Report.md). Khoảng thời gian Doctor điều khiển chu kỳ kiểm tra sức khỏe.

## Biến máy chủ điều khiển

Máy chủ điều khiển là nơi Manager áp dụng/thu hồi Principle và cài/tạm ngưng Decision.

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `SERVER_HTTPS_ENABLE` | `true` | Bật HTTPS cho API điều khiển. |
| `SERVER_LOGGER_FILE_ENABLE` | `false` | Bật tệp nhật ký máy chủ. |
| `SERVER_LOGGER_FILE_PATH` | `storage/logs/server.log` | Đường dẫn tệp hợp lệ. |
| `SERVER_LOGGER_FORMAT` | Mẫu tích hợp sẵn | Chuỗi tối đa 2048 ký tự. |
| `SERVER_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Múi giờ hợp lệ. |
| `SERVER_PORT` | `9947` | Cổng điều khiển `1..65535`. |
| `SERVER_CONTROLLER_PATH_PREFIX` | `api/v1` | Tiền tố đường dẫn, không có đoạn `.`/`..`. |
| `SERVER_CONTROLLER_PATH_PRINCIPLES` | `principles` | Điểm cuối Principle. |
| `SERVER_CONTROLLER_METHOD_APPLY` | `put` | Phương thức áp dụng: `post`, `put`, `patch`, `delete`. |
| `SERVER_CONTROLLER_METHOD_REVOKE` | `delete` | Phương thức thu hồi. |
| `SERVER_CONTROLLER_PATH_DECISIONS` | `decisions` | Điểm cuối Decision. |
| `SERVER_CONTROLLER_METHOD_IMPLEMENT` | `put` | Phương thức cài đặt. |
| `SERVER_CONTROLLER_METHOD_SUSPEND` | `delete` | Phương thức tạm ngưng. |
| `SERVER_CONTROLLER_AUTHORIZATION_EMAIL` | `X-Executor` | Tiêu đề HTTP mang email người thực thi. |
| `SERVER_SECURITY_MANAGER` | `worker` | Tên/định danh manager được Defender tin cậy. |
| `SERVER_SECURITY_USERNAME` | `defly-defender` | Tên đăng nhập Basic Auth, tối thiểu 4 ký tự. |
| `SERVER_SECURITY_PASSWORD` | `P@55w0rd` | Mật khẩu Basic Auth, tối thiểu 4 ký tự. |

Manager dựng URL điều khiển theo:

```text
http(s)://<defender.name>:<SERVER_PORT>/<PATH_PREFIX>/<resource-path>
```

Manager gửi Basic Auth và tiêu đề HTTP chứa người thực thi. Khi không tắt xác minh TLS, Manager tìm chứng chỉ tại `storage/tls/defenders/<defender-name>.crt` theo cấu hình hiện hành.

## Biến Proxy

| Biến | Mặc định | Ý nghĩa |
| --- | --- | --- |
| `PROXY_BACKEND_URL` | `http://localhost` | URL máy chủ phía sau được proxy ngược bảo vệ. |
| `PROXY_LOGGER_FILE_ENABLE` | `false` | Bật tệp nhật ký proxy. |
| `PROXY_LOGGER_FILE_PATH` | `storage/logs/proxy.log` | Đường dẫn tệp hợp lệ. |
| `PROXY_LOGGER_FORMAT` | Mẫu tích hợp sẵn | Chuỗi tối đa 2048 ký tự. |
| `PROXY_LOGGER_TIMEZONE` | `Asia/Ho_Chi_Minh` | Múi giờ hợp lệ. |
| `PROXY_PORT` | `9948` | Cổng proxy `1..65535`. Khi triển khai, Orchestrator ghi đè bằng `proxy_port` của bản ghi. |
| `PROXY_TRUSTED_ENABLE` | `false` | Bật danh sách proxy tin cậy. |
| `PROXY_TRUSTED_LIST` | `null` | Danh sách IP/CIDR phân cách bằng dấu phẩy. |
| `PROXY_PRESERVE_HOST` | `true` | Giữ tiêu đề HTTP `Host` khi chuyển tiếp tới máy chủ phía sau. |
| `PROXY_SEVERITY_INFO` | `1` | Điểm Action `suspect(info)`. |
| `PROXY_SEVERITY_NOTICE` | `2` | Điểm mức notice. |
| `PROXY_SEVERITY_WARNING` | `3` | Điểm mức warning. |
| `PROXY_SEVERITY_ERROR` | `4` | Điểm mức error. |
| `PROXY_SEVERITY_CRITICAL` | `5` | Điểm mức critical. |
| `PROXY_SEVERITY_ALERT` | `6` | Điểm mức alert. |
| `PROXY_SEVERITY_EMERGENCY` | `7` | Điểm mức emergency. |
| `PROXY_VIOLATION_LEVEL` | `1` | Cấp độ khởi đầu của [Principle](Principle.md). |
| `PROXY_VIOLATION_SCORE` | `5` | Ngưỡng/điểm cấu hình nền; bộ kiểm tra yêu cầu `5..100000`. |

Điểm theo mức độ nghiêm trọng nhận số nguyên `1..1000`; cấp độ vi phạm nhận `1..1000000`.

## Vòng đời triển khai

`deployment_status` có bốn giá trị:

| Trạng thái | Ý nghĩa |
| --- | --- |
| `pending` | Yêu cầu triển khai/hủy đã được đưa vào hàng đợi. |
| `processing` | Tác vụ hoặc Orchestrator đang xử lý. |
| `failed` | Yêu cầu, thao tác Docker hoặc quá trình triển khai gặp lỗi. |
| `successful` | Container đã được tạo thành công. |

Luồng triển khai:

1. Manager đặt `pending` và gửi tác vụ `DefenderDeployment` vào hàng đợi.
2. Tác vụ đặt `processing` rồi gọi Orchestrator.
3. Orchestrator đọc bản ghi Defender từ cơ sở dữ liệu, chuẩn hóa đối tượng biến môi trường và kiểm tra image đã tồn tại.
4. Container cũ cùng tên được xóa, ổ dữ liệu nhật ký/lỗi được tạo, ổ dữ liệu TLS phải tồn tại.
5. Orchestrator thêm `DEFENDER_NAME`, ghi đè `PROXY_PORT`, công bố cổng và khởi động container với chính sách khởi động lại `unless-stopped`.
6. Thành công đặt `successful`; lỗi đặt `failed` cùng chi tiết/ngoại lệ/nhật ký.

Không thể triển khai lại khi trạng thái đang là `pending` hoặc `processing`. Defender `successful` không được xóa trực tiếp; cần hủy trước.

## Quan hệ Docker Compose

Orchestrator đọc các nhãn và mạng của chính container đang chạy. Defender động được:

- Gắn vào mọi mạng của ngữ cảnh Compose hiện tại, mạng đầu tiên tại lúc tạo và các mạng còn lại sau đó.
- Gắn các nhãn `com.docker.compose.project`, `service`, `config-hash`, `oneoff` và siêu dữ liệu dự án khi ngữ cảnh có đủ nhãn.
- Dùng tên ổ dữ liệu có tiền tố dự án Compose.

Nhờ các nhãn Compose này, Defender động thuộc dự án hiện tại và lệnh `docker compose down` của dự án có thể nhận diện/gỡ container đó. Nếu Orchestrator chạy ngoài ngữ cảnh Compose hoặc không đọc được container hiện tại qua Docker API, nó chỉ gắn nhãn Defly và không có ngữ cảnh dự án/mạng để kế thừa.

## Hủy và theo dõi

`cancel` chỉ được xếp hàng từ Manager khi Defender đang `successful`. Orchestrator buộc xóa container theo tên. Khi thành công, Manager xóa `status`, `details`, `deployment_status` và `deployment_details`; ổ dữ liệu nhật ký/lỗi không bị mã nguồn hủy xóa.

`follow` gọi Orchestrator để lấy đầu ra chuẩn/đầu ra lỗi của container và trả tối đa 100 dòng cuối. Nó không thay đổi trạng thái triển khai.

## Trạng thái sức khỏe

`status` (`normal`/`abnormal`) và `details` mô tả trạng thái sức khỏe/Doctor, độc lập với trạng thái triển khai. Một container có thể triển khai thành công nhưng sức khỏe bất thường; ngược lại bản ghi chưa có dữ liệu sức khỏe có thể có `status = null`.

Không dùng `deployment_status` để thay cho kiểm tra sức khỏe.

## Quản lý chính sách

Defender có hai quan hệ có thứ tự:

| Tài nguyên | Cờ bảng nối | Hoạt động khi |
| --- | --- | --- |
| [Principle](Principle.md) | `is_applied` | Gắn + áp dụng thành công. |
| [Decision](Decision.md) | `is_implemented` | Gắn + cài đặt thành công. |

### Principle

- Chỉ Principle có trạng thái kiểm tra `passed` được áp dụng qua luồng chuẩn.
- Defender phải có trạng thái triển khai `successful`.
- Áp dụng/thu hồi chạy qua hàng đợi và API điều khiển.
- Cờ bảng nối chỉ đổi sau phản hồi HTTP thành công.

### Decision

- Defender phải có trạng thái triển khai `successful`.
- Cài đặt/tạm ngưng chạy qua hàng đợi và API điều khiển.
- Cờ bảng nối chỉ đổi sau phản hồi HTTP thành công.

`last_response_details` lưu riêng nhánh `principle` và `decision`, gồm hành động, ID tài nguyên, email người yêu cầu, mã trạng thái/phản hồi HTTP hoặc ngoại lệ, và thời điểm phản hồi.

Gắn chỉ tạo quan hệ; áp dụng/cài đặt mới kích hoạt trong quá trình chạy. Nên thu hồi/tạm ngưng trước khi tháo quan hệ nếu tài nguyên đang hoạt động.

## Report

Defender có nhiều [Report](Report.md) qua `reports.created_by`. Trong ngữ cảnh Report, `created_by` trỏ đến Defender, không trỏ User như các tài nguyên quản trị khác.

## Danh sách kiểm tra triển khai

- Dùng tên Defender hợp lệ và phân giải được từ mạng của Manager/container.
- Bảo đảm `proxy_port` chưa bị chiếm trên máy chủ.
- Cấu hình URL máy chủ phía sau và thông tin xác thực cơ sở dữ liệu đúng mạng Docker.
- Tạo sẵn ổ dữ liệu/chứng chỉ TLS cần thiết.
- Bảo đảm Orchestrator có Docker socket cùng các nhãn/mạng Compose.
- Chờ `successful` trước khi áp dụng Principle hoặc cài Decision.
- Phân biệt trạng thái sức khỏe, trạng thái triển khai và trạng thái chính sách trong bảng nối.
