# Defender

Defender vừa là bản ghi cấu hình trong Manager, vừa là WAF/proxy ngược được tạo từ
bản ghi đó. Trang này định nghĩa mô hình dữ liệu và các trạng thái bất biến. Việc tạo container thuộc
[Hướng dẫn Orchestrator](../Orchestrator-Guide.md), thực thi lưu lượng thuộc
[Hướng dẫn Defender](../Defender-Guide.md), còn khóa biến môi trường chính xác thuộc
[Biến môi trường](../Environment-Variables.md#biến-của-defender).

## Các trường

| Trường | Chủ sở hữu | Ý nghĩa |
| --- | --- | --- |
| `name` | User | Định danh chữ thường dùng dấu gạch ngang, duy nhất để liên kết bản ghi, container, chứng chỉ và tên máy chủ điều khiển |
| `proxy_port` | User | Cổng máy chủ được công bố cho lưu lượng được bảo vệ |
| `environment_variables` | User/hệ thống | Cấu hình chung, máy chủ điều khiển và proxy đã được kiểm tra, lưu trong một đối tượng JSON |
| `description` | User | Ngữ cảnh quản trị |
| `deployment_status` | Quy trình triển khai | Tiến độ tạo/hủy |
| `deployment_details` | Quy trình triển khai | Kết quả hoặc lỗi có thể xử lý |
| `status` | Khi Defender chạy | Sức khỏe do Doctor ghi nhận (`normal`, `abnormal` hoặc chưa biết) |
| `details` | Khi Defender chạy | Bằng chứng sức khỏe |
| `last_response_details` | Quy trình điều khiển chính sách | Phản hồi áp dụng/thu hồi/cài đặt/tạm ngưng gần nhất |

Manager hiển thị biến môi trường theo các nhóm khóa cố định. User sửa giá trị, không thêm khóa
tùy ý; giá trị hệ thống như định danh Defender và cổng proxy hiệu lực được đưa vào lúc
triển khai.

## Ba chiều trạng thái độc lập

Không gom các trạng thái sau thành một cờ “đang chạy”.

### Trạng thái triển khai

| Giá trị | Ý nghĩa |
| --- | --- |
| `pending` | Công việc đã vào hàng đợi |
| `processing` | Worker/Orchestrator đang thực hiện |
| `failed` | Thao tác triển khai lỗi; xem `deployment_details` |
| `successful` | Container đã được tạo thành công |
| `null` | Chưa có kết quả triển khai đang hoạt động |

Bản ghi `pending`/`processing` không được triển khai lại. Bản ghi `successful` phải hủy trước
khi xóa.

### Sức khỏe khi chạy

`status` và `details` đến từ Doctor khi Defender chạy. Triển khai thành công vẫn có
thể không khỏe; bản ghi trong cơ sở dữ liệu trông bình thường không chứng minh đường đi proxy/máy chủ phía sau
hoạt động. Dùng kiểm tra theo tầng tại [Vận hành](../Operations.md).

### Kích hoạt chính sách

Gắn và kích hoạt là hai việc riêng:

| Quan hệ | Cờ bảng nối có thứ tự | Hoạt động khi |
| --- | --- | --- |
| [Principle](Principle.md) | `is_applied` | Đã gắn, kiểm tra và áp dụng thành công |
| [Decision](Decision.md) | `is_implemented` | Đã gắn và cài đặt thành công |

Gắn chỉ tạo ý định triển khai. Các thao tác áp dụng/thu hồi và cài đặt/tạm ngưng trong
Manager gọi API điều khiển Defender; bản ghi nối chỉ đổi sau phản hồi thành công. Hãy
thu hồi/tạm ngưng trước khi gỡ mục đang hoạt động.

## Ràng buộc chính sách

- Chỉ Principle có trạng thái kiểm tra `passed` đi vào luồng áp dụng chuẩn.
- Áp dụng/cài đặt yêu cầu Defender đã triển khai thành công.
- Principle và Decision giữ thứ tự quan hệ.
- Principle/Decision bị khóa khi còn gắn vào bất kỳ Defender nào.
- Kiểm tra Principle và mọi thao tác vòng đời/điều khiển chính sách Defender là quy trình
  thủ công trong Manager.

`last_response_details` giữ phản hồi điều khiển mới nhất riêng cho Principle và Decision
để không nhầm kết quả triển khai với kết quả chính sách.

## Truy cập có Guard

Defender có thể được bảo vệ bằng một hoặc nhiều [Guard](Guard.md). Nếu không gắn Guard,
Permission và quy tắc quy trình thông thường quyết định thao tác có được tiếp tục hay
không. Nếu có bất kỳ Guard nào được gắn, User hiện tại/người yêu cầu phải là chủ sở hữu
qua `created_by` hoặc thuộc ít nhất một Guard chưa hết hạn gắn với Defender đó.

Guard ảnh hưởng các thao tác quản trị và đường điều khiển, không ảnh hưởng việc khớp lưu
lượng khi Defender chạy. Defender có Guard cũng bị ẩn khỏi phạm vi liệt kê/tìm kiếm trừ khi
User là chủ sở hữu hoặc thuộc Guard còn hiệu lực khớp. User root và Permission `Defender:all`
vẫn cần quyền sở hữu hoặc tư cách thành viên Guard khớp khi Defender đã được bảo vệ bằng Guard.

## Report

Defender sở hữu nhiều [Report](Report.md). Trong quan hệ này,
`reports.created_by` nhận diện Defender đã quan sát request, không phải User quản trị.

## Trước khi triển khai

Kiểm tra dữ liệu đầu vào ở mức mô hình:

- tên duy nhất và phân giải được;
- cổng proxy trên máy chủ chưa dùng;
- URL máy chủ phía sau/cơ sở dữ liệu hợp lệ từ mạng của Defender;
- Principle đã kiểm tra và Decision có chủ đích;
- giá trị biến môi trường vượt qua kiểm tra của Manager.

Sau đó dùng [Cấu hình](../Configuration.md) cho giao kèo giữa các dịch vụ,
[Hướng dẫn Orchestrator](../Orchestrator-Guide.md) cho hành vi container và
[Khắc phục sự cố](../Troubleshooting.md) khi có lỗi.
