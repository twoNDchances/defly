# Các khái niệm cốt lõi

Phần này giải thích các đối tượng mà bạn gặp trong Manager và Defender. Nếu mới làm quen với Defly, nên đọc theo thứ tự dưới đây thay vì bắt đầu từ biểu mẫu cấu hình.

## Quản trị và truy cập

1. [User](User.md): tài khoản đăng nhập và chủ sở hữu cấu hình.
2. [Group](Group.md): nhóm người dùng hoặc khóa API để cấp quyền tập trung.
3. [Permission](Permission.md): quyền thao tác trên tài nguyên Manager.
4. [Guard](Guard.md): ranh giới người vận hành theo từng Defender cho các hành động được bảo vệ.
5. [Key](Key.md): thông tin xác thực cho tích hợp API.
6. [Label](Label.md): siêu dữ liệu dùng để phân loại tài nguyên.

## Chính sách WAF

Một chính sách được xây theo hướng từ dữ liệu đến hành động:

1. [Wordlist](Wordlist.md) và [Pattern](Pattern.md) cung cấp dữ liệu có thể tái sử dụng.
2. [Target](Target.md) chọn vị trí dữ liệu trong vòng đời HTTP.
3. [Engine](Engine.md) chuẩn hóa hoặc đổi kiểu giá trị của Target.
4. [Rule](Rule.md) so sánh giá trị Target với dữ liệu cấu hình.
5. [Action](Action.md) mô tả việc cần làm khi điều kiện đúng.
6. [Principle](Principle.md) nhóm nhiều Rule bằng toán tử AND trong cùng giai đoạn.
7. [Decision](Decision.md) dùng tổng điểm để đưa ra phán quyết cuối cho yêu cầu hoặc phản hồi.
8. [Defender](Defender.md) là chương trình nhận và thực thi các Principle cùng Decision này.

## Theo dõi và điều tra

- [Report](Report.md) lưu bằng chứng của một sự kiện tường lửa.
- [Timeline](Timeline.md) lưu lịch sử thao tác quản trị trong Manager.

Hai loại dữ liệu này phục vụ mục đích khác nhau: Report mô tả lưu lượng trong quá trình chạy, còn Timeline mô tả ai đã thay đổi cấu hình.
