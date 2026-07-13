# Bắt đầu nhanh

Trang này bắt đầu sau khi Defly đã được cài và các dịch vụ hoạt động tốt. Lệnh cài đặt và
yêu cầu hệ thống nằm trong [Cài đặt](Installation.md); chẩn đoán dịch vụ nằm trong
[Vận hành](Operations.md#kiểm-tra-sức-khỏe-theo-từng-lớp).

## 1. Đăng nhập và xem bảng điều khiển

Mở Manager, đăng nhập bằng tài khoản khởi tạo và xác nhận phần cơ sở dữ liệu, hàng đợi,
Orchestrator, Defender không báo lỗi hạ tầng. Đổi mật khẩu khởi tạo tạm thời trước
khi tạo thêm người dùng.

## 2. Thiết lập quyền truy cập

Tạo một vai trò người vận hành với quyền tối thiểu trước khi thêm nhiều người dùng; tự động hóa nên
dùng API Key thay vì mật khẩu dùng chung. Làm theo các bước cụ thể tại
[Quản trị truy cập](Manager-Guide.md#quản-trị-truy-cập); thứ tự ưu tiên quyền thuộc
các trang khái niệm User, Group, Permission, Guard và Key được dẫn từ đó.

## 3. Tạo chính sách đầu tiên an toàn

Dùng [Xây dựng chính sách](Manager-Guide.md#xây-dựng-chính-sách) cho thứ tự thao tác
trên biểu mẫu và [Khái niệm cốt lõi](CoreConcepts/README.md) cho quy tắc mô hình dữ liệu. Ở lần đầu,
chỉ chọn một tín hiệu yêu cầu hẹp, dùng `log` hoặc `report` thay vì `deny`, kiểm tra
Principle và xử lý hết lỗi. Cách này tạo bằng chứng quan sát mà không dễ gây gián đoạn.

## 4. Tạo Defender đầu tiên

Làm theo [Tạo và triển khai Defender](Manager-Guide.md#tạo-và-triển-khai-defender).
Với Defender đầu tiên, dùng tên duy nhất, cổng proxy chưa bị chiếm và URL máy chủ phía sau truy
cập được từ bên trong container. Chờ `deployment_status=successful` rồi mới áp dụng hoặc
cài đặt chính sách. Ý nghĩa từng trạng thái thuộc [Defender](CoreConcepts/Defender.md).

## 5. Xác minh hành vi

Gửi lưu lượng kiểm thử qua proxy Defender rồi kiểm tra theo thứ tự:

- máy chủ phía sau nhận được lưu lượng được cho phép;
- nhật ký Defender không có lỗi truyền tải/khi chạy;
- Rule dự kiến đã khớp;
- Action dự kiến tạo log hoặc Report;
- điểm và Decision đúng với kịch bản kiểm thử.

Chỉ đổi Action sang `deny` sau khi trường hợp đúng, sai, sai định dạng và đi vòng đều cho kết quả
mong đợi. Khi một tầng lỗi, tra [Khắc phục sự cố](Troubleshooting.md) theo triệu chứng.
