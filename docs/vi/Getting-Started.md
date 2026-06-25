# Bắt đầu nhanh

Trang này bắt đầu sau khi Defly đã được cài và các service khỏe mạnh. Lệnh cài đặt và
yêu cầu hệ thống nằm trong [Cài đặt](Installation.md); chẩn đoán service nằm trong
[Vận hành](Operations.md#kiểm-tra-sức-khỏe-theo-từng-lớp).

## 1. Đăng nhập và xem Dashboard

Mở Manager, đăng nhập bằng tài khoản bootstrap và xác nhận phần database, queue,
Orchestrator, Defender không báo lỗi hạ tầng. Đổi password bootstrap tạm thời trước
khi tạo thêm user.

## 2. Thiết lập quyền truy cập

Tạo một role operator với quyền tối thiểu trước khi thêm nhiều user; automation nên
dùng API Key thay vì password dùng chung. Làm theo các bước cụ thể tại
[Quản trị truy cập](Manager-Guide.md#quản-trị-truy-cập); thứ tự ưu tiên quyền thuộc
các trang concept User, Group, Permission và Key được dẫn từ đó.

## 3. Tạo policy đầu tiên an toàn

Dùng [Xây dựng chính sách](Manager-Guide.md#xây-dựng-chính-sách) cho thứ tự thao tác
trên form và [Khái niệm cốt lõi](CoreConcepts/README.md) cho quy tắc model. Ở lần đầu,
chỉ chọn một tín hiệu request hẹp, dùng `log` hoặc `report` thay vì `deny`, validate
Principle và xử lý hết lỗi. Cách này tạo bằng chứng quan sát mà không dễ gây outage.

## 4. Tạo Defender đầu tiên

Làm theo [Tạo và triển khai Defender](Manager-Guide.md#tạo-và-triển-khai-defender).
Với instance đầu tiên, dùng tên duy nhất, proxy port chưa bị chiếm và backend URL truy
cập được từ bên trong container. Chờ `deployment_status=successful` rồi mới apply hoặc
implement policy. Ý nghĩa từng state thuộc [Defender](CoreConcepts/Defender.md).

## 5. Xác minh hành vi

Gửi traffic kiểm thử qua proxy Defender rồi kiểm tra theo thứ tự:

- backend nhận traffic được allow;
- log Defender không có lỗi transport/runtime;
- Rule dự kiến đã match;
- Action dự kiến tạo log hoặc Report;
- score và Decision đúng với test case.

Chỉ đổi Action sang `deny` sau khi case đúng, sai, malformed và bypass đều cho kết quả
mong đợi. Khi một tầng lỗi, tra [Khắc phục sự cố](Troubleshooting.md) theo triệu chứng.
